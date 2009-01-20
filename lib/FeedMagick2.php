<?php
/**
 * @package FeedMagick2
 * @author l.m.orchard@pobox.com
 * @version 0.1
 */

/**
 * Set up a simple class autoload handler for FeedMagick2 apps.
 */
function __autoload ($class_name) {
    $file_name = str_replace('_', DIRECTORY_SEPARATOR, $class_name) . '.php';
    $status = (@include_once $file_name);
}

/**
 * Main driver framework for FeedMagick2
 */
class FeedMagick2 {

    /** List of modules found in base package, not in scan of module paths */
    public static $builtin_modules = array(
        'FeedMagick2_Pipeline'
    );

    /** A shared static instance of this class */
    public static $instance = FALSE;

    /** Current pipeline for the instance */
    public $pipeline;

    public $config;
    public $log;
    public $cache;
    public $headers;
    public $template;
    public $urlmapper;

    /**
     * Initialize the framework context
     * @param array Configuration array
     * @todo Try to replace Services_JSON with the PHP binary extension, but not working on my laptop.
     */
    public function __construct($config=NULL) {
        
        $this->config = ($config) ? $config : array();
        
        $this->base_dir = $this->getConfig('base_dir', '.');
        
        $this->log = $this->getLogger('main');
        $this->log->debug(basename($_SERVER['SCRIPT_FILENAME'])." starting up...");
        
        $this->cache = new Cache_Lite($this->getConfig('cache', array(
            'cacheDir' => './data/cache/', 'lifeTime' => '3600'
        )));
        
        $this->json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);

        self::$instance = $this;

    }

    /**
     * Return a global shared instance.
     *
     * @return FeedMagick2
     */
    public function getInstance() {
        if (!self::$instance) 
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * Return a usable logger given a name.
     *
     * @param string Name to be used in identifying log messages.
     * @return Log an instance of the logger
     */
    public function getLogger($name) {
        return Log::singleton(
            'file', 
            $this->getConfig('log/path', './logs/main.log'),
            $name, 
            array(), 
            $this->getConfig('log/level', PEAR_LOG_DEBUG)
        );
    }

    /**
     * Fetch a configuration setting value.  Arrays of arrays can be navigated 
     * with '/' delimited keys.
     * 
     * @param string Name of the configuration setting
     * @param string Default config value if setting not set
     * @return mixed configuration value
     */
    function getConfig($name, $default=NULL) {
        $parts = explode('/', $name);
        $curr  = $this->config;
        foreach ($parts as $part) {
            if (array_key_exists($part, $curr)) {
                $curr = $curr[$part];
            } else {
                return $default;
            }
        }
        return $curr;
    }

    /**
     * Collect the metadata from a pipe module.
     *
     * @param string Name of the pipe module class
     * @return array of metadata properties.
     */
    public function getMetaForModule($class_name) {
        $rc = new ReflectionClass($class_name);
        return $this->parseMetaFromModuleFile($rc->getFileName());
    }
    
    /**
     * Collect metadata from all registered pipe modules.
     * 
     * @return array of module metadata
     */
    public function getMetaForModules() {
        $metas = array();
        $modules = $this->findModules();
        foreach ($modules as $fn) {
            $meta = $this->parseMetaFromModuleFile($fn);
            if ($meta) $metas[$meta['class']] = $meta;
        }
        return $metas;
    }

    /**
     * Create a new object instance of a pipe module.
     * 
     * @param string Name of the module to be instantiated
     * @param string ID string for the instance
     * @param array Options for the instance, may be indexed or associative array
     * @return BasePipeModule a new instance of the requested pipe module.
     */
    public function instantiateModule($class_name, $id, $options) {
        $obj = new $class_name($this, $id, $options);
        return $obj;
    }

    /**
     * Scan the module paths and come up with the filenames of available modules.
     *
     * @return array List of module filenames.
     */
    public function findModules() {

        $modules = array();
        
        foreach(self::$builtin_modules as $module_class) {
            $rc = new ReflectionClass($module_class);
            $modules[] = $rc->getFileName();
        }        
        
        $modules_paths = $this->getConfig('paths/modules', "{$this->base_dir}/modules");
        if (!is_array($modules_paths)) 
            $modules_paths = array($modules_paths);

        foreach ($modules_paths as $modules_path) {
            if (is_dir($modules_path)) {
                if ($dh = opendir($modules_path)) {
                    while (($name = readdir($dh)) !== FALSE) {
                        $file = "$modules_path/$name";
                        if (is_file($file) && strpos(substr($file, -4, 4), '.php') !== FALSE) {
                            $modules[] = $file;
                        }
                    }
                    closedir($dh);
                }
            }
        }

        return $modules; 
    }    

    /**
     * Given a filename, attempt to parse header comment for module meta 
     * attribute content.
     *
     * @param string Filename for a pipeline module.
     */
    public function parseMetaFromModuleFile($fn) {

        $src = file($fn, FILE_USE_INCLUDE_PATH | FILE_IGNORE_NEW_LINES);
        $meta = array( 'description' => '');
        $in_header = FALSE;
        $past_header = FALSE;

        foreach ($src as $line) {
            $line = trim($line);
            if (!$past_header && $line == '/**') {

                // Found the start of the header comment block, so start processing.
                $in_header = TRUE;

            } else if ($line == '*/') {

                // Found the end of the header comment block, so stop processing.
                $in_header = FALSE;
                $past_header = TRUE;

            } else if (!$in_header) {

                if (substr($line, 0, 5) == 'class') {
                    // Try to extract the class name.
                    $parts = split(' ', $line);
                    $meta['class'] = $parts[1];
                }

            } else if ($in_header && substr($line, 0, 1) == '*') {
                
                // Lines starting with an asterisk are part of the comment.
                $line = trim(substr($line, 1));
                if (substr($line, 0, 1) == '@') {

                    // Comment lines starting with an @ are named attributes.
                    $parts = split(' ', $line, 2);
                    if (count($parts == 2)) {
                        // As long as there's a name and a value, extract the attribute.
                        $name = substr($parts[0], 1);
                        if (isset($meta[$name])) {
                            if (!is_array($meta[$name]))
                                $meta[$name] = array($meta[$name]);
                            $meta[$name][] = $parts[1];
                        } else {
                            $meta[$name] = $parts[1];
                        }
                    }

                } else {
                    
                    // This line isn't named attribute, so it's title or description.
                    if (!isset($meta['title']) && $line) {
                        // If there's no title yet, and the line is non-blank, capture the title.
                        $meta['title'] = $line;
                    } else {
                        // All other lines pile into the description.
                        $meta['description'] .= "$line\n";
                    }

                }
            }
        }

        return $meta;
    }

    /**
     * Scan the pipelines directory for pipelines and return metadata for all 
     * of them.
     *
     * @return array List of pipelines
     */
    public function getMetaForPipelines() {
        $out = array();
        $path = $this->getConfig('paths/pipelines', "{$this->base_dir}/pipelines");
        if ( is_dir($path) && ($dh = opendir($path)) ) {
            while (($name = readdir($dh)) !== FALSE) {
                $opts = $this->getMetaForPipeline($name);
                if ($opts) {
                    $out[$name] = $opts;
                } else {
                    $this->log->err("Failed fetching pipeline '$name'!");
                }
            }
            closedir($dh);
        }
        return $out;
    }

    /**
     * Return metadata for a named pipeline.
     * 
     * @param string Name of a pipeline
     * @return array Associative array of pipeline metadata
     */
    public function getMetaForPipeline($name) {
        $path = $this->getConfig('paths/pipelines', "{$this->base_dir}/pipelines");
        $file = "$path/$name";
        $src  = file_get_contents($file);
        $opts = $this->json->decode($src, TRUE);
        $this->log->debug("Fetching pipeline '$file'...");
        return $opts;
    }

    /**
     * Fetch data from a local file if available, or try from the web if the 
     * path appears not valid for local access.
     * 
     * @param string Path to desired data
     * @param string Base root path to which access should be restricted
     * @return array headers and body
     * @todo Figure out if any file system based headers make sense here. (modified, etc)
     */
    public function fetchFileOrWeb($base, $path) {
        
        // Get the full path from the absolute base path.        
        $realbase = realpath($base);
        $fullpath = "$realbase/$path";

        if ( $fullpath == ($realpath = realpath($fullpath)) ) {
            // If the realpath and the derived fullpath are the same, then the 
            // path is valid and usable for local access.
            $this->log->debug("Fetching locally: $realpath");
            $headers = array();
            $body    = file_get_contents($realpath);
        } else {
            // Assume any other path not matching local file system is a URL.
            $this->log->debug("Fetching via HTTP: $path");
            $req     =& new HTTP_CachedRequest($path);
            $rv      = $req->sendRequest();
            $headers = $req->getResponseHeader();
            $body    = $req->getResponseBody();

            // $this->log->debug("...was stale? ".$req->_is_stale." was cached? ".$req->_cache_hit);
        }

        return array($headers, $body);
    }

    /**
     * Quick and dirty CLI wrapper around pipeline dispatch, accepts "--name 
     * value" options as query parameters, STDIN as POST request body on 
     * '--stdin' flag.
     */
    public function clidispatch() {
        chdir($this->base_dir);
        
        $_SERVER['REQUEST_METHOD'] = 'CLI';

        $argv = $_SERVER['argv'];
        $script_name = array_shift($argv);

        // Convert --options into $_GET values, super-hacky getopts.
        while ($opt = array_shift($argv)) {
            if (substr($opt, 0, 2) == '--') {
                $opt_name = substr($opt, 2);
                if ($argv && !(substr($argv[0], 0, 2) == '--')) {
                    // If there are args left, and the next one doesn't start 
                    // with '--', treat as an arg value
                    $_GET[$opt_name] = array_shift($argv);
                } else {
                    // Otherwise, just treat this arg as a boolean flag.
                    $_GET[$opt_name] = TRUE;
                }
            }
        }

        // $this->webdispatch();
        $this->dispatchPipeline($_GET['pipeline']);
    }
    
    /**
     * Web request dispatcher, using URL routing and controller dispatching.
     * 
     * @todo Carry headers along as a property of this object?
     * @todo Need better error handling here.
     */
    public function webdispatch() {
        
        chdir($this->base_dir);

        $this->urlmapper = Net_URL_Mapper::getInstance();
        $this->urlmapper->setPrefix($this->getConfig('base_url', '/'));

        $m = $this->urlmapper;     

        $m->connect('/pipelines/:name', 
            array( 'controller'=>'pipeline' ));
        $m->connect('/', 
            array( 'controller'=>'action', 'action'=>'index' ));
        $m->connect('/phpinfo', 
            array( 'controller'=>'action', 'action'=>'phpinfo' ));
        $m->connect('/inspect/*path', 
            array( 'controller'=>'action', 'action'=>'inspect' ));
        $m->connect('/help/', 
            array( 'controller'=>'action', 'action'=>'help', 'path'=>'README' ));
        $m->connect('/help/*path', 
            array( 'controller'=>'action', 'action'=>'help' ));
        $m->connect('*path', 
            array( 'controller'=>'action', 'action'=>'default' ));

        if (! $this->route_vars = $m->match($_SERVER['REQUEST_URI']) ) {
            $this->route_vars = array('controller'=>'action', 'action'=>'index');
        }

        switch ($this->getRouteVar('controller')) { 
            case 'pipeline':
                return $this->dispatchPipeline($this->route_vars['name']);
            case 'action':
            default:
                if ( $this->route_vars['action'] == 'index' 
                        && isset($_GET['pipeline']) ) {
                    return $this->dispatchPipeline($_GET['pipeline']);
                }
                return $this->dispatchAction();
        }

    }

    /**
     * Fetch a route parameter extracted from a matched route.
     * 
     * @param string Name of the variable
     * @param string Default value if variable not set
     * @return string route parameter
     */
    public function getRouteVar($name, $default=NULL) {
        return (isset($this->route_vars) && array_key_exists($name, $this->route_vars)) ?
            $this->route_vars[$name] : $default;
    }

    /**
     * Controller function to dispatch pipeline processing.
     */
    public function dispatchPipeline($pipeline_name='default') {
        
        // Grab the desired pipeline by local file or URL.
        list($pipeline_headers, $pipeline_src) = $this->fetchFileOrWeb(
            $this->getConfig('paths/pipelines', "{$this->base_dir}/pipelines"),
            $pipeline_name
        );

        // Attempt to parse the pipeline.
        // $pipeline_opts = json_decode($pipeline_src, TRUE);
        $pipeline_opts = $this->json->decode($pipeline_src, TRUE);
        if (!$pipeline_opts) {
            $this->log->err("Error parsing pipeline definition.");
            die("Error parsing pipeline definition.");
        }

        // Build the root pipeline and run it.
        $pipe = new FeedMagick2_Pipeline($this, 'main', $pipeline_opts);
        $this->log->debug("Fetching output from pipeline.");
        list($headers, $body) = $pipe->fetchOutput_Raw();

        $this->log->debug("Sending output.");
        foreach ($headers as $name=>$value) { header("$name: $value"); }
        echo $body;
         
    }

    /**
     * For routes that lead to actions, locate the action, initialize 
     * templating, and execute the action.
     */
    public function dispatchAction() {

        $actions_base = $this->getConfig('paths/actions', './actions');

        $action_name = $this->getRouteVar('action', 'default');

        if (! ($action_path = realpath("$actions_base/$action_name.action.php")) ) {
            // If no action was found with the desired name, fall back to default.
            $action_path = realpath("$actions_base/default.action.php");
        }

        $this->template = $tpl = new FeedMagick2_Template();
        $this->template->BASE_URL =
            $this->getConfig('base_url', '/');
        $this->template->setPath('template', 
            $this->getConfig('path/templates', './templates'));
        $this->template->setPath('resource', 
            $this->getConfig('path/templates', './templates'));

        include $action_path;

    }

}
