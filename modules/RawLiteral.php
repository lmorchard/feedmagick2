<?php
/**
 * @package FeedMagick2
 * @subpackage PipeModules
 * @author l.m.orchard@pobox.com
 * @version 0.1
 */

/** */
require_once 'FeedMagick2.php';
require_once 'FeedMagick2/BasePipeModule.php';

/**
 * A module that passes raw content through unchanged.
 */
class RawLiteral extends FeedMagick2_BasePipeModule {

    public function getVersion()     
        { return '0.0'; }
    public function getTitle()
        { return "Raw Literal"; }
    public function getDescription() 
        { return 'Literal raw data contained in options.'; }
    public function getAuthor()
        { return 'l.m.orchard@pobox.com'; }
    public function getSupportedInputs() 
        { return array( 'Raw' ); }

    /**
     *
     */
    public function __construct($parent, $id=NULL, $options=array()) {
        parent::__construct($parent, $id, $options);
        $this->headers = array();
        $this->body = '';
    }

    public function setHeaders($headers) {
        $this->headers = $headers;
    }

    public function setBody($body) {
        $this->body = $body;
    }

    /** 
     * Simply fetch and pass through raw data from input module.
     * @return array ($headers, $body) - Raw headers and body data.
     */
    public function fetchOutput_Raw() {
        if (!$this->headers) $this->headers = $this->getParameter("headers");
        if (!$this->body) $this->body = $this->getParameter("body");
        return array($this->headers, $this->body);
    }

}

/** Register this module with the system. */
FeedMagick2::registerModule('RawLiteral');
