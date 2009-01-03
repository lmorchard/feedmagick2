<?php
/**
 * ReadingListBlender
 *
 * Blend entries from feeds listed in an OPML outline given as input.
 *
 * @param string url
 * @param string format [rss, atom]
 * @param bool   munge_titles
 *
 * @name Blender
 * @package FeedMagick2
 * @subpackage PipeModules
 * @author l.m.orchard@pobox.com
 * @version 0.1
 */
class ReadingListBlender extends FeedMagick2_DOMBasePipeModule {

    /**
     * Namespace and element name tuples defining known feed item containers
     */
    public static $ITEM_CONTAINERS = array(
        array('','channel'),
        array('http://purl.org/rss/1.0/','channel'),
        array('http://purl.org/atom/ns#','feed'),
        array('http://www.w3.org/2005/Atom','feed')
    );

    /**
     * Namespace and element name tuples defining known feed items
     */
    public static $ITEMS = array(
        array('','item'),
        array('','entry'),
        array('http://purl.org/rss/1.0/','item'),
        array('http://purl.org/atom/ns#','entry'),
        array('http://www.w3.org/2005/Atom','entry')
    );

    /**
     * Grab feeds from each of the sub-modules and import items from each into 
     * this module's input feed.
     * @return array ($headers, $doc)
     */
    public function processDoc($headers, $doc) {

        $format = $this->getParameter('format', 'rss');

        list($list_meta, $feeds) = $this->parseReadingList($doc);
        $this->log->debug("Found ".count($feeds)." feeds in list");

        // Generate blank feed as a basis for the blend.
        list($bf_headers, $doc) = $this->generateBlankFeed($list_meta);
        $container = $this->findElements($doc, self::$ITEM_CONTAINERS)->item(0);

        // Look up all the feeds for blending from incoming OPML feed.
        foreach ($feeds as $feed) {
            
            // Fetch feed from list.
            list($feed_headers, $feed_body) = 
                $this->fetchFeed($feed['xmlUrl']);

            // Tidy feed if necessary and parse into DOM document.
            $sub_doc = $this->tidyAndParseFeed($feed_body);
            if ($sub_doc) {

                // If the feed needs normalizing into the output format, do so.
                $sub_doc = $this->normalizeSubFeed($sub_doc);

                // Find and process all items found in this sub-feed.
                $items = $this->findElements($sub_doc, self::$ITEMS);
                for ($i=0; $i<$items->length; $i++) {

                    // Import and copy the item into the master feed.
                    $item = $doc->importNode($items->item($i), TRUE);
                    $container->appendChild($item);

                    // TODO: Streamline this repetitive code for each format.

                    if ($format == 'rss') {
                        
                        if (!!($this->getParameter('munge_titles', TRUE)) && isset($feed['text'])) {
                            $titles = $item->getElementsByTagName('title');
                            if ($titles->length) {
                                $title = $titles->item(0);
                            } else {
                                $title = $doc->createElement('title');
                                $item->insertBefore($title, $item->firstChild);
                            }
                            $title->insertBefore(
                                $doc->createTextNode('['.$feed['text'].'] '),
                                $title->firstChild
                            );
                        }

                        $this->append($item, 'source', 
                            array('url' => $feed['xmlUrl']), $feed['text']);

                    } else if ($format == 'atom') {

                        if (!!($this->getParameter('munge_titles', TRUE)) && isset($feed['text'])) {
                            $titles = $item->getElementsByTagNameNS(self::$ATOM_NS, 'title');
                            if ($titles->length) {
                                $title = $titles->item(0);
                            } else {
                                $title = $doc->createElementNS(self::$ATOM_NS, 'title');
                                $item->insertBefore($title, $item->firstChild);
                            }
                            $title->insertBefore(
                                $doc->createTextNode('['.$feed['text'].'] '),
                                $title->firstChild
                            );
                        }
                        
                        // TODO: Copy more metadata from atom feeds?
                        $source = $this->append($item, 
                            array(self::$ATOM_NS, 'source'), NULL, '');
                        $this->append($source, array(self::$ATOM_NS, 'link'), 
                            array('rel'=>'via', 'href'=>$feed['xmlUrl']));
                        $this->append($source, array(self::$ATOM_NS, 'title'), 
                            NULL, $feed['text']);
                    
                    }

                    // Munge item titles?

                }
            }
        }

        if ($format == 'rss') {
            
            list($headers, $doc) = 
                $this->sortRSSByPubdate($feed_headers, $doc);
            $limiter = new SortLimiter($this->getParent(), $this->getId()."-limiter", array(
                'limit' => $this->getParameter('limit', '50')
            ));
            list($headers, $doc) = $limiter->processDoc($feed_headers, $doc);

        } else {

            // TODO: Date sorting in Atom is buggy.  Fix this.
            $limiter = new SortLimiter($this->getParent(), $this->getId()."-limiter", array(
                'limit'     => $this->getParameter('limit', '50'),
                'sortby'    => $this->getParameter('sortby', 'atom:published'),
                'sortorder' => $this->getParameter('sordorder', 'desc')
            ));
            list($headers, $doc) = $limiter->processDoc($feed_headers, $doc);

        }


        $headers = array(
            'Content-Type' => $bf_headers['Content-Type']
        );

        return array($headers, $doc);
    }

    /**
     * Generate a blank starter feed in the appropriate format.
     */
    public function generateBlankFeed($list_meta) {
        $params = array_merge($list_meta, $this->getParameters());
        $blank_feed = new BlankFeed(
            $this->getParent(), $this->getId().'-blank', $params
        );
        return $blank_feed->fetchOutput_DOM_XML();
    }

    /**
     *
     */
    public function sortRSSByPubdate(&$headers, $doc) {
        $sorter = new XSLFilter($this->getParent(), $this->getId()."-normal-rss", array(
            'xsl' => 'sort-rss-by-pubdate.xsl', 'format' => 'rss'
        ));
        return $sorter->processDoc($feed_headers, $doc);
    }

    /**
     * Roughly scan an OPML outline, extracting all feeds
     * into a flat list of associative arrays.
     *
     * @param string An OPML document
     * @return array List of associative arrays containing node attributes.
     */
    public function parseReadingList($doc) {

        $head_data = array();
        $heads = $doc->getElementsByTagName('head');
        if ($heads->length && $attrs = $heads->item(0)->childNodes) {
            for ($j=0; $j<$attrs->length; $j++) {
                $attr = $attrs->item($j);
                if ($attr->nodeType == 1) {
                    $head_data[$attr->nodeName] = 
                        $attr->firstChild->nodeValue;
                }
            }
        }

        $data = array();
        $nodes = $doc->getElementsByTagName('outline');
        for ($i=0; $i<$nodes->length; $i++) {
            $extract = array();
            $attrs = $nodes->item($i)->attributes;
            for ($j=0; $j<$attrs->length; $j++) {
                $attr = $attrs->item($j);
                $extract[$attr->name] = $attr->value;
            }
            if (array_key_exists('xmlUrl', $extract))
                $data[] = $extract;
        }

        return array($head_data, $data);
    }

    /**
     *
     */
    public function fetchFeed($feed_url) {
        $this->log->debug("Reading list fetching ".$feed_url);
        list($feed_headers, $feed_body) = $this->getParent()->fetchFileOrWeb(
            $this->getParent()->getConfig('paths/web', $this->getParent()->base_dir."/www"),
            $feed_url
        );
        return array($feed_headers, $feed_body);
    }

    /**
     *
     */
    public function tidyAndParseFeed($feed_body) {
        // HACK: Turn off error reporting for now.
        $old_err = error_reporting(0);
        
        // Attempt to parse the fetched feed data.
        $doc =& new DOMDocument();
        if ($doc->loadXML($feed_body) === FALSE) { 
            if (extension_loaded('tidy')) {
                // If we have tidy, be "liberal" by attempting to repair the 
                // XML and parsing again.  This works most of the time.
                $tidy = new tidy;
                $tidy->parseString($feed_body, array(
                    'input-xml' => true, 'output-xml' => true
                ));
                $tidy->cleanRepair();
                if (!$doc->loadXML((string)$tidy)) {
                    return FALSE;
                }
            } else {
                return FALSE;
            }
        }

        // HACK: Restore error reporting level.
        error_reporting($old_err);
        return $doc;
    }

    /**
     * If necessary, normalize the given feed into the appropriate format.
     */
    public function normalizeSubFeed($sub_doc) {
        // TODO: Improve this quick and dirty normalizing short-circuit
        $format = $this->getParameter('format', 'rss');
        if ($format=='atom' && $sub_doc->firstChild->nodeName=='feed')
            return $sub_doc;
        if ($format=='rss' && $sub_doc->firstChild->nodeName=='rss')
            return $sub_doc;

        // Normalize feed to output format
        $normalizer = new XSLFilter($this->getParent(), $this->getId()."-normal", array(
            'xsl' => 'normalizer.xsl', 'format' => $format
        ));
        list($headers, $sub_doc) = $normalizer->processDoc($feed_headers, $sub_doc);
        
        return $sub_doc;
    }

    /**
     * Search a DOM doc for a list of element criteria.
     * @param DOMDocument A DOM document ot search
     * @param array Tuple of namespace and element name
     * @return DOMNodeList A list of DOMNode results
     */
    protected function findElements($doc, $criteria) {
        foreach ($criteria as $tuple) {
            list($ns, $name) = $tuple;
            $eles = ($ns) ? 
                $doc->getElementsByTagNameNS($ns, $name) : 
                $doc->getElementsByTagName($name);
            if ($eles->length) return $eles;
        }
        return NULL;
    }

}
