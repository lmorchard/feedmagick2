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
     * @param array feed headers
     * @param DOMDocument a DOM document ready for processing
     * @return array ($headers, $doc)
     */
    function processDoc($headers, $doc) {
        // No-op in abstract class.
        return array($headers, $doc);
    }

    /**
     * Helper function to construct and append a DOM node all in one shot.  Had 
     * some trouble using SimpleXML with cdata containing ampersands, so this 
     * is a homebrewed workaround.
     *
     * @param $parent - Parent node or DOMDocument
     * @param $tagnname - Name of element to construct
     * @param $attributes - Array of name/value pairs to be used as attributes (optional)
     * @param $cdata - Character data to be inserted into element (optional)
     * @return A newly constructed DOM element, added to the parent.
     */
    public static function append($parent, $tagname, $attributes=NULL, $cdata=NULL) {

        // Find the owner document, whether it's the actual parent or the owner 
        // of the parent.
        $doc = ($parent instanceof DOMDocument) ?  $parent : $parent->ownerDocument;

        if (is_array($tagname)) {
            // If the $tagname is an array, assume that it's a (name, NS) tuple.
            $el = $doc->createElementNS($tagname[1], $tagname[0]);
        } else {
            // Otherwise, it's just a string tag name.
            $el = $doc->createElement($tagname);
        }

        // Add any attributes supplied.
        if ($attributes) {
            foreach ($attributes as $name=>$val) {
                $el->setAttribute($name, $val);
            }
        }

        // Insert any character data supplied.
        if ($cdata) {
            $el->appendChild($doc->createTextNode($cdata));
        }

        // Finally, append this element to its parent and return the element itself.
        $parent->appendChild($el);
        return $el;
    }

    function xpathVal($xpath, $path, $node) {
        $nodes = $xpath->query($path, $node);
        if ($nodes->length > 0) {
            return $nodes->item(0)->nodeValue;
        }
        return "";
    }

    /**
     * Hook this object up in-line with other DOM filters
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
