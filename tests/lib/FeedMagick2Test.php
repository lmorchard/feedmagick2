<?php
global $config;
require_once 'conf/config.php';
require_once 'FeedMagick2.php';
require_once 'PHPUnit/Framework.php';
 
class FeedMagick2Test extends PHPUnit_Framework_TestCase { 

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
    
    protected function setUp() {
        global $config;
        $this->feedmagick2 = new FeedMagick2($config);
        $this->base_dir    = $this->feedmagick2->getConfig('base_dir');
        $this->xml_dir     = $this->base_dir."/xml";
    }

    protected function buildXPath($doc, $namespaces=array()) {

        // Gather namespaces for XPath, merging parameter list with defaults.
        $namespaces = array_merge( self::$default_namespaces, $namespaces );

        // Build an XPath object and set up its namespaces.
        $this->xpath = $xpath = new DOMXPath($doc);
        foreach ($namespaces as $name=>$url) {
            if ($url) $xpath->registerNamespace($name, $url);
        }

        return $xpath;

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
?>
