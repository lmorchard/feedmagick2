<?php
/**
 * @package FeedMagick2
 * @author l.m.orchard@pobox.com
 * @version 0.1
 */

/** */
require_once 'XML/SaxFilters.php';
require_once 'XML/SaxFilters/IO/StringWriter.php';
require_once 'XML/SaxFilters/IO/StringReader.php';

/**
 * Abstract base class for all pipe modules.
 */
class FeedMagick2_BasePipeModule {

    const PARAM_STRING   = 1;
    const PARAM_REQUIRED = 256;

    private $_parent;
    private $_id;
    private $_options;
    private $_input;
    private $_params;

    /** Accessor to module title */
    public function getTitle() 
        { return 'Abstract base class for all pipe modules.'; }
    /** Accessor to module description */
    public function getDescription() 
        { return ''; }
    /** Accessor to module version */
    public function getVersion()
        { return '0.0'; }
    /** Accessor to module author */
    public function getAuthor() 
        { return 'l.m.orchard@pobox.com'; }
    /** Accessor to expected module parameters */
    public function getExpectedParameters() 
        { return array(); }
    /** Accessor to supported module inputs */
    public function getSupportedInputs()
        { return array(); }

    /** Construct an instance of the pipe module. */
    public function __construct($parent, $id=NULL, $options=array()) {
        $this->_parent  = $parent;
        $this->_id      = $id;
        $this->_options = $options;
        $this->_input   = NULL;
        $this->_params  = array();

        $this->log = $parent->getLogger("$id");
    }

    /** Fetch the current ID for this module instance. */
    public function getId() { return $this->_id; }

    /** Connect another module instance as this instance's input */
    public function setInputModule($input) { return $this->_input = $input; } 

    /** Accessor to this instance's input module */
    public function getInputModule() { return $this->_input; } 

    /** 
     * Return a list of outputs offered by this object, by default introspected 
     * from 'fetchOutput_'-prefixed methods.
     */
    public function &getSupportedOutputs() { 
        $outputs = array();
        $ro = new ReflectionObject($this);
        $methods = $ro->getMethods();
        $pre = 'fetchOutput_';
        foreach ($methods as $method) {
            $name = $method->getName();
            if (strpos($name, $pre) !== FALSE)
                array_push($outputs, substr($name, strlen($pre)));
        }
        return $outputs; 
    }

    /** Gather metadata for this instance in one convenient array. */
    public function &getMetadata() {
        $meta = array();
        $methods = array( 
            'version'     => 'getVersion', 
            'title'       => 'getTitle', 
            'description' => 'getDescription', 
            'author'      => 'getAuthor', 
            'parameters'  => 'getExpectedParameters',
            'inputs'      => 'getSupportedInputs', 
            'outputs'     => 'getSupportedOutputs'
        );
        foreach ($methods as $name=>$method) {
            $meta[$name] = $this->$method();
        }
        return $meta;
    }

    /** 
     * Return a pipe module parameters.
     * @param $name - Parameter name
     * @param $default - Parameter default to return if none set.
     * @todo Honor GET parameters here?
     */
    public function getParameter($name, $default=NULL) {
        if (isset($this->_options[$name])) {
            return $this->_options[$name];
            /* } else { */
            /** @todo Honor GET parameters here. */
        } else {
            return $default;
        }
    }

    /**
     * Return raw content, NULL in abstract base class.
     * @return array($headers, $data) - headers and raw data
     */
    public function fetchOutput_Raw() {
        return array(NULL, NULL);
    }

    /**
     * Parse output from fetchOutput_Raw() and return a DOM doc
     * @return array($headers, $doc) - headers and a DOM doc
     */
    public function fetchOutput_DOM_XML() {
        list($headers, $body) = $this->fetchOutput_Raw();
        $doc = new DOMDocument();
        $doc->loadXML($body);
        return array($headers, $doc);
    }

    /**
     * Parse output from fetchOutput_Raw() and return a SAX parser
     * @return array($headers, $parser) - headers and a SAX parser
     */
    public function fetchOutput_SAX_XML() {
        list($headers, $body) = $this->fetchOutput_Raw();
        $parser = XML_SaxFilters_createParser('expat','string',$body);
        $sax_filter = new XML_SaxFilters_AbstractFilter();
        $sax_filter->parser = $parser;
        $parser->setChild($sax_filter);
        return array($headers, $sax_filter);
    }

}
