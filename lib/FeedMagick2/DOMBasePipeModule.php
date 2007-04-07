<?php
/**
 * @package FeedMagick2
 * @author l.m.orchard@pobox.com
 * @version 0.1
 */

/** */
require_once 'FeedMagick2.php';
require_once 'FeedMagick2/BasePipeModule.php';

/**
 * A base class for DOM-based feed manipulation modules.
 */
class FeedMagick2_DOMBasePipeModule extends FeedMagick2_BasePipeModule {

    public function getVersion()     
        { return '0.0'; }
    public function getTitle()
        { return "DOM Base Filter"; }
    public function getDescription() 
        { return 'A base class for DOM-based pipe modules'; }
    public function getAuthor()
        { return 'l.m.orchard@pobox.com'; }
    public function getSupportedInputs() 
        { return array( 'DOM_XML' ); }

    /**
     * Perform any needed modifications on the DOMDocument.
     * @param $headers - feed headers
     * @param $doc - a DOMDocument ready for processing
     * @return array ($headers, $doc)
     */
    function processDoc($headers, $doc) {
        // No-op in abstract class.
        return array($headers, $doc);
    }

    /**
     * Hook this object up in-line with other SAX filters
     * @return array ($headers, $doc)
     */
    function fetchOutput_DOM_XML() {
        list($headers, $doc) = $this->getInputModule()->fetchOutput_DOM_XML();
        list($headers, $new_doc) = $this->processDoc($headers, $doc);
        return array($headers, $new_doc);
    }
    
    /** 
     * Simply fetch and pass through raw data from input module.
     * @return array ($headers, $body)
     */
    public function fetchOutput_Raw() {
        list($headers, $dom) = $this->fetchOutput_DOM_XML();
        return array($headers, $dom->saveXML());
    }

}
