<?php
/**
 * RawLiteral
 *
 * Module that injects body and headers from parameters.
 *
 * @package FeedMagick2
 * @subpackage PipeModules
 * @author l.m.orchard@pobox.com
 * @version 0.1
 */
class RawLiteral extends FeedMagick2_BasePipeModule {

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
        if (!$this->headers) 
            $this->headers = $this->getParameter("headers");
        if (!$this->body) 
            $this->body = $this->getParameter("body");
        return array($this->headers, $this->body);
    }

}
