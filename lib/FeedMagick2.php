<?php
/**
 * @package FeedMagick2
 * @author l.m.orchard@pobox.com
 * @version 0.1
 */

/** */
require_once 'Log.php';
require_once 'Net/URL/Mapper.php';
require_once 'Cache/Lite.php';
require_once 'HTTP/Request.php';
require_once 'Services/JSON.php';
require_once 'FeedMagick2/Template.php';
require_once 'FeedMagick2/BasePipeModule.php';
require_once 'FeedMagick2/Pipeline.php';

/**
 * Main driver framework for FeedMagick2
 */
class FeedMagick2 {

    protected static $module_registry;

    /** Current pipeline for the instance */
    public $pipeline;

    public $config;
    public $log;
    public $cache;
    public $headers;
    public $template;
    public $urlmapper;

    /**
     * Initialize the web framework
     * @param array Configuration array
     * @todo Try to replace Services_JSON with the PHP binary extension, but not working on my laptop.
     */
    public function __construct($config) {
        $this->config = $config;
        $this->base_dir = $this->getConfig('base_dir', '.');
        $this->log = $this->getLogger('main');
        $this->log->debug(basename($_SERVER['SCRIPT_FILENAME'])." starting up...");
        $this->cache = new Cache_Lite($this->getConfig('cache', array(
            'cacheDir' => './data/cache/', 'lifeTime' => '3600'
        )));
        $this->json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        $this->loadModules();
    }

    /**
     * Return a usable logger given a name.
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
     * Register a loaded and known pipe module.
     * @param string Name of a pipe module class to register.
     * @todo Allow modules to register aliases, ie. FeedMagic2_Pipeline => Pipeline
     */
    public static function registerModule($class_name) {
        if (!isset(self::$module_registry)) 
            self::$module_registry = array();
        array_push(self::$module_registry, $class_name);
    }

    /**
     * Collect the metadata from a pipe module.
     * @param string Name of the pipe module class
     * @return array of metadata properties.
     */
    public function &getMetaForModule($class_name) {
        $obj =& $this->instantiateModule($class_name, NULL, NULL);
        return $obj->getMetadata();
    }
    
    /**
     * Collect metadata from all registered pipe modules.
     * @return array of module metadata
     */
    public function &getMetaForModules() {
        $metas = array();
        foreach (self::$module_registry as $class_name) {
            $metas[$class_name] = $this->getMetaForModule($class_name);
        }
        return $metas;
    }

    /**
     * Create a new object instance of a pipe module.
     * @param string Name of the module to be instantiated
     * @param string ID string for the instance
     * @param array Options for the instance, may be indexed or associative array
     * @return BasePipeModule a new instance of the requested pipe module.
     */
    public function &instantiateModule($class_name, $id, $options) {
        if (!in_array($class_name, self::$module_registry)) 
            die("No such pipe module named '$class_name'");
        $this->log->debug("$id instantiated as module $class_name");
        $rc = new ReflectionClass($class_name);
        $obj = $rc->newInstance($this, $id, $options);
        return $obj;
    }

    /**
     * Scan the modules path and load available modules.
     * @todo Do some more verification of available modules.
     * @todo Wrap module loading in a try/catch and report bum modules.
     * @todo Find a way to autoload these?
     */
    public function loadModules() {
        $modules_path = $this->getConfig('paths/modules', "{$this->base_dir}/modules");
        if (is_dir($modules_path)) {
            if ($dh = opendir($modules_path)) {
                while (($name = readdir($dh)) !== FALSE) {
                    $file = "$modules_path/$name";
                    if (is_file($file) && strpos(substr($file, -4, 4), '.php') !== FALSE) {
                        $this->log->debug("Loading module '$file'...");
                        require_once $file;
                    }
                }
                closedir($dh);
            }
        }
    }

    /**
     * Fetch data from a local file if available, or try from the web if the 
     * path appears not valid for local access.
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
            $req     =& new HTTP_Request($path);
            $rv      = $req->sendRequest();
            $headers = $req->getResponseHeader();
            $body    = $req->getResponseBody();
        }

        return array($headers, $body);
    }

    /**
     * Quick and dirty CLI wrapper around web dispatch, accepts "--name value" 
     * options as query parameters, STDIN as POST request body on '--stdin' 
     * flag.
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
        $m->connect('*path', 
            array( 'controller'=>'action', 'action'=>'default' ));

        if (! $this->route_vars = $m->match($_SERVER['REQUEST_URI']) ) {
            $this->route_vars = array('controller'=>'action', 'action'=>'index');
        }

        switch ($this->route_vars['controller']) {
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
     *
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
     *
     */
    public function dispatchAction() {

        $action_name = isset($this->route_vars['action']) ? 
            $this->route_vars['action'] : 'default';

        $actions_base = $this->getConfig('paths/actions', './actions');

        if (! $action_path = realpath("$actions_base/$action_name.action.php") ) {
            $action_path = realpath("$actions_base/default.action.php");
        }

        $this->template = new FeedMagick2_Template();
        $this->template->BASE_URL =
            $this->getConfig('base_url', '/');
        $this->template->setPath('template', 
            $this->getConfig('path/templates', './templates'));
        $this->template->setPath('resource', 
            $this->getConfig('path/templates', './templates'));

        $tpl = $this->template;

        $tpl->page_id = 'pageid';
         
        include $action_path;

    }

}
