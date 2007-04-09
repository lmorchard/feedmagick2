<?php
/**
 * @package FeedMagick2
 * @author l.m.orchard@pobox.com
 * @version 0.1
 */

/** */
require_once 'Log.php';
require_once 'Cache/Lite.php';
require_once 'HTTP/Request.php';
require_once 'Services/JSON.php';
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

    /**
     * Initialize the web framework
     * @param array Configuration array
     * @todo Try to replace Services_JSON with the PHP binary extension, but not working on my laptop.
     */
    public function __construct($config) {
        $this->config = $config;
        $this->log = $this->getLogger('main');
        $this->log->debug(basename($_SERVER['SCRIPT_FILENAME'])." starting up...");
        $this->pipeline = array();
        $this->cache = new Cache_Lite($this->getConfig('cache', array(
            'cacheDir' => './data/cache/', 'lifeTime' => '3600'
        )));
        $this->json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        $this->headers = array();
        $this->loadModules();
    }

    /**
     * Return a usable logger given a name.
     * @param string Name to be used in identifying log messages.
     */
    public function getLogger($name) {
        $log_conf = $this->getConfig('log', array(
            'path' => 'logs/main.log', 'level' => PEAR_LOG_INFO
        ));
        return Log::singleton(
            'file', $log_conf['path'], $name, array(), $log_conf['level']
        );
    }

    /**
     * Fetch a configuration setting value.
     * @param string Name of the configuration setting
     * @param string Default config value if setting not set
     */
    function getConfig($name, $default=NULL) {
        return isset($this->config[$name]) ? $this->config[$name] : $default;
    }

    /**
     * Register a loaded and known pipe module.
     * @param string Name of a pipe module class to register.
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
        $modules_path = $this->getConfig('modules_path', './modules');
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
     * @todo Carry headers along as a property of this object?
     * @todo Jail / constrain local path in file_get_contents below.
     * @todo Need better error handling here.
     */
    public function dispatch() {

        // Get the pipeline URL, defaulting to index.json
        $pipeline_url = isset($_GET['pipeline']) ? $_GET['pipeline'] : 'default';

        if (strpos($pipeline_url, 'http://') === 0 || strpos($pipeline_url, 'https://') === 0) {
            $this->log->debug("Fetching pipeline via HTTP from $pipeline_url");
            // If the URL starts with http:// or https://, do a web fetch.
            $req =& new HTTP_Request($pipeline_url);
            $rv = $req->sendRequest();
            $pipeline_src = $req->getResponseBody();
        } else {
            $this->log->debug("Fetching pipeline from local file from $pipeline_url");
            // Otherwise, treat this as a path to a local pipeline
            $pipelines_path = $this->getConfig('pipelines_path', 'pipelines');
            $pipeline_src = file_get_contents("$pipelines_path/$pipeline_url");
        }

        // Attempt to parse the pipeline.
        // $pipeline_opts = json_decode($pipeline_src, TRUE);
        $pipeline_opts = $this->json->decode($pipeline_src, TRUE);
        if (!$pipeline_opts) {
            $this->log->error("Error parsing pipeline definition.");
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

}
