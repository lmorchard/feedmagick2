<?php
/**
 * @package FeedMagick2
 * @subpackage PipeModules
 * @author l.m.orchard@pobox.com
 * @version 0.1
 */

/** */
require_once 'FeedMagick2.php';
require_once 'FeedMagick2/DOMBasePipeModule.php';

/**
 * Use XPath to include or exclude feed items.
 * @todo This could probably be done with XSL, but implemented this way will work without libxsl.
 */
class SortLimiter extends FeedMagick2_DOMBasePipeModule {

    public function getVersion()     
        { return '0.0'; }
    public function getTitle()
        { return "SortLimiter"; }
    public function getDescription() 
        { return 'Sort items by XPath values and limit the number of items in the feed'; }
    public function getAuthor()
        { return 'l.m.orchard@pobox.com'; }
    public function getSupportedInputs() 
        { return array( 'DOM_XML' ); }
    public function getExpectedParameters() {
        return array(
            'sortby'    => 'string',
            'sortorder' => 'enum:asc,desc',
            'limit'     => 'string',
            'offset'    => 'string'
        );
    }

    /**
     * An XPath used to find feed items for processing.
     */
    static $items_path = "/atom03:feed/atom03:entry | /atom10:feed/atom10:entry | /rdf:RDF/rss10:item | /rdf:RDF/rss09:item | /rss/channel/item";

    /**
     * List of prefix / URL pairs defining XPath namespaces.
     */
    static $default_namespaces = array(
        'atom03'  => 'http://purl.org/atom/ns#',
        'atom10'  => 'http://www.w3.org/2005/Atom',
        'atom'    => 'http://www.w3.org/2005/Atom',
        'rss10'   => 'http://purl.org/rss/1.0/',
        'rdf'     => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
        'dc'      => 'http://purl.org/dc/elements/1.1/',
        'sy'      => 'http://purl.org/rss/1.0/modules/syndication/',
        'admin'   => 'http://webns.net/mvcb/',
        'content' => 'http://purl.org/rss/1.0/modules/content/',
        'rss09'   => 'http://my.netscape.com/rdf/simple/0.9/',
        'dcterms' => 'http://purl.org/dc/terms/',
        'content' => 'http://purl.org/rss/1.0/modules/content/',
        'l'       => 'http://purl.org/rss/1.0/modules/link/',
        'xhtml'   => 'http://www.w3.org/1999/xhtml'
    );

    /**
     * Use includes and excludes to decide on a case-by-case basis whether feed 
     * items should be rejected or remain in the feed.
     * @param array Feed headers
     * @param DOMDocument The feed document
     * @return array headers and doc
     */
    public function processDoc($headers, $doc) {

        // Gather namespaces for XPath, merging parameter list with defaults.
        $namespaces = array_merge(
            self::$default_namespaces,
            $this->getParameter('namespaces', array())
        );

        // Build an XPath object and set up its namespaces.
        $this->xpath = $xpath = new DOMXPath($doc);
        foreach ($namespaces as $name=>$url) {
            if ($url) $xpath->registerNamespace($name, $url);
        }

        // Scoop up the parameters in the pipeline.
        $sortby    = $this->getParameter('sortby', 'pubDate');
        $sortorder = $this->getParameter('sortorder', 'asc') == 'asc';
        $limit     = $this->getParameter('limit', FALSE);
        $offset    = $this->getParameter('offset', 0);

        // Iterate through the items in the feed and index by xpath value.
        $idx_items = array();
        $items = $xpath->query(self::$items_path);
        foreach ($items as $item) {
            $key = $this->xpathVal($sortby, $item);
            $idx_items[$key] = $item;
        }

        // Remove all the items from the feed
        $parent = NULL;
        foreach (array_values($idx_items) as $item) {
            $parent = $item->parentNode;
            $parent->removeChild($item);
        }

        // Sort the orphaned feed items by key, according to sort order.
        if ($sortorder) {
            ksort($idx_items);
        } else {
            krsort($idx_items);
        }

        // Re-add sorted items back into the feed, minding the offset and limit 
        // parameters.
        foreach ($idx_items as $key => $item) {
            if ($offset > 0) {
                $offset--;
            } else {
                if ($limit !== FALSE) {
                    if ($limit <= 0) {
                        break;
                    } else {
                        $limit--;
                    }
                }
                $parent->appendChild($item);
            }
        }    

        return array($headers, $doc);
    }

    /**
     * Get the value for an XPath matched against a node.
     * @param string XPath to match
     * @param DOMNode node against which to match
     * @return string the result from the XPath.
     */
    function xpathVal($path, $node) {
        $nodes = $this->xpath->query($path, $node);
        return ($nodes->length > 0) ? $nodes->item(0)->nodeValue : '';
    }

}

/** Register this module with the system. */
FeedMagick2::registerModule('SortLimiter');
