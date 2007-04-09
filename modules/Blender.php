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
 * Blends the items from output feeds of one or more sub-modules into this 
 * module's input feed.  Does no conversion of any kind, so mixed feed formats 
 * will mean mixed format items in the output feed.
 *
 * @todo Auto-convert sub-module feeds into format of input feed?
 */
class Blender extends FeedMagick2_DOMBasePipeModule {

    public function getVersion()     
        { return '0.0'; }
    public function getTitle()
        { return "Blender"; }
    public function getDescription() 
        { return "Blend entries from the results of several sub-modules."; }
    public function getAuthor()
        { return 'l.m.orchard@pobox.com'; }
    public function getSupportedInputs() 
        { return array( 'DOM_XML' ); }
    public function getExpectedParameters()
        { return array(); }

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
     * Build this module and all the sub-modules to be used in feed blending.
     * @param BasePipeModule Parent pipe module
     * @param string ID for this module
     * @param array An array of module definitions
     */
    public function __construct($parent, $id=NULL, $options=array()) {
        parent::__construct($parent, $id, $options);
        if ($options) {
            $this->_modules = array();
            $i = 0;
            foreach ($options as $opt) {
                $mod = $parent->instantiateModule(
                    $opt['module'], 
                    isset($opt['id']) ? $opt['id'] : $id.'-seg'.($i++), 
                    $opt['parameters']
                );
                array_push($this->_modules, $mod);
            }
        }
    }

    /**
     * Search a DOM doc for a list of element criteria.
     * @param DOMDocument A DOM document ot search
     * @param array Tuple of namespace and element name
     * @return DOMNodeList A list of DOMNode results
     */
    protected function _findElements($doc, $criteria) {
        foreach ($criteria as $tuple) {
            list($ns, $name) = $tuple;
            $eles = ($ns) ? 
                $doc->getElementsByTagNameNS($ns, $name) : 
                $doc->getElementsByTagName($name);
            if ($eles->length) return $eles;
        }
        return NULL;
    }

    /**
     * Grab feeds from each of the sub-modules and import items from each into 
     * this module's input feed.
     * @return array ($headers, $doc)
     */
    public function processDoc($headers, $doc) {

        // Try looking for the input feed's item container.
        $containers = $this->_findElements($doc, self::$ITEM_CONTAINERS);
        if ($containers->length) {
            $container = $containers->item(0);

            // Grab the DOM feed from each sub-module built for the blender.
            foreach ($this->_modules as $module) {
                list($sub_headers, $sub_doc) = $module->fetchOutput_DOM_XML();

                // Import any items found from each sub-module into the main input feed.
                $items = $this->_findElements($sub_doc, self::$ITEMS);
                if ($items->length) {
                    for ($i=0; $i<$items->length; $i++) {
                        $item = $doc->importNode($items->item($i), TRUE);
                        $container->appendChild($item);
                    }
                }

            }
        }
        return array($headers, $doc);
    }

}

/** Register this module with the system. */
FeedMagick2::registerModule('Blender');
