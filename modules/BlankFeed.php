<?php
/**
 * Blank Feed
 *
 * Provide a blank RSS or Atom feed as starting input to the next module.
 *
 * @name Blender
 * @package FeedMagick2
 * @subpackage PipeModules
 * @author l.m.orchard@pobox.com
 * @version 0.1
 */
class BlankFeed extends FeedMagick2_DOMBasePipeModule {

    /**
     *
     */
    function fetchOutput_DOM_XML() {
        $headers = array();
        $doc = new DOMDocument();
        switch ($this->getParameter('format', 'rss20')) {
            case 'rss': 
            case 'rss20': 
                $this->buildRSS20Feed($headers, $doc); break;
            case 'atom':  
                $this->buildAtomFeed($headers, $doc); break;
        }
        return array($headers, $doc);
    }

    /**
     *
     */
    function buildRSS20Feed($headers, $doc) {
        $headers['Content-Type'] = 'application/rss+xml';

        $rss = $this->append($doc, 'rss', array( 'version'=>'2.0'));
        $channel = $this->append($rss, 'channel');

        $names = array( 
            'title', 'link', 'description', 'pubDate', 
            'generator', 'language', 'webMaster', 'managingEditor', 
            'copyright', 'lastBuildDate', 'category'
        );
        foreach($names as $name) {
            $value = $this->getParameter($name);
            if ($value) 
                $this->append($channel, $name, NULL, $value);
        }
    }

    /**
     *
     */
    function buildAtomFeed($headers, $doc) {
        
        $headers['Content-Type'] = 'application/atom+xml';

        $atom = $this->append($doc, array(self::$ATOM_NS, 'feed'));

        $names = array( 
            'id', 'title', 'subtitle', 'updated'
        );
        foreach($names as $name) {
            $value = $this->getParameter($name);
            if ($value) 
                $this->append($atom, array(self::$ATOM_NS, $name), NULL, $value);
        }

    }

}
