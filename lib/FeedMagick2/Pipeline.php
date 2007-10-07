<?php
/**
 * Pipeline
 *
 * Implements a complete pipeline, also itself a pipeline module.
 * 
 * @package FeedMagick2
 * @subpackage PipeModules
 * @author l.m.orchard@pobox.com
 * @version 0.1
 * @inputs Raw
 * @outputs Raw, DOM, SAX
 *
 * @todo Methods to prepend or append pipe modules, post-construction
 */

require_once 'FeedMagick2.php';
require_once 'FeedMagick2/BasePipeModule.php';

/**
 */
class FeedMagick2_Pipeline extends FeedMagick2_BasePipeModule {

    public function getSupportedInputs() {
        if ($this->_pipe) {
            return $this->getHead()->getSupportedInputs();
        } else {
            return array('Raw');
        }
    }

    /** Head of the pipe */
    private $_head;
    /** Tail of the pipe */
    private $_tail;
    /** List of modules in the pipe itself. */
    private $_pipe;

    /** Construct an instance of the pipe module. */
    public function __construct($parent, $id=NULL, $options=array()) {
        parent::__construct($parent, $id, $options);
        if ($options) {
            if (array_key_exists('pipeline', $options)) {
                // Accept a bare list of modules, or a full-blown pipeline spec with metadata.
                $options = $options['pipeline'];
            } 
            $this->_pipe = array();
            list($i, $prev_mod, $mod) = array(0, NULL, NULL);
            foreach ($options as $opt) {
                $mod = $parent->instantiateModule(
                    $opt['module'], 
                    $this->getId()."-".(isset($opt['id']) ? $opt['id'] : 'seg'.($i++)), 
                    isset($opt['parameters']) ? $opt['parameters'] : array()
                );
                if ($prev_mod) $mod->setInputModule($prev_mod);
                array_push($this->_pipe, $prev_mod = $mod);
            }
        }
    }

    /**
     * Grab the tail module of the pipe.
     * @return Pipe module
     */
    public function &getTail() {
        return $this->_pipe[count($this->_pipe)-1];
    }

    /**
     * Grab the head module of the pipe.
     * @return Pipe module
     */
    public function &getHead() {
        return $this->_pipe[0];
    }

    /** Connect another module instance as this instance's input */
    public function setInputModule($input) { 
        return $this->getHead()->setInputModule($input);
    } 

    /** 
     * Fetch raw output from tail of the pipe.
     * @return array Raw headers and body data.
     */
    public function fetchOutput_Raw() {
        return $this->getTail()->fetchOutput_Raw();
    }

    /** 
     * Fetch DOM parsed output from tail of the pipe.
     * @return array Raw headers and DOM doc
     */
    public function fetchOutput_DOM_XML() {
        return $this->getTail()->fetchOutput_DOM_XML();
    }

    /** 
     * Fetch SAX filter from tail of the pipe.
     * @return array Raw headers and SAX filter
     */
    public function fetchOutput_SAX_XML() {
        return $this->getTail()->fetchOutput_SAX_XML();
    }

}
