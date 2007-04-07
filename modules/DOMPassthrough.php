<?php
/**
 *
 *
 * @package FeedMagick2
 * @subpackage PipeModules
 * @author l.m.orchard@pobox.com
 * @version 0.1
 */

/** */
require_once 'FeedMagick2.php';
require_once 'FeedMagick2/DOMBasePipeModule.php';

/**
 *
 */
class DOMPassthrough extends FeedMagick2_DOMBasePipeModule {

    public function getVersion()     
        { return '0.0'; }
    public function getTitle()
        { return "DOM Passthrough"; }
    public function getDescription() 
        { return 'Passthrough of DOM parsed XML.'; }
    public function getAuthor()
        { return 'l.m.orchard@pobox.com'; }
    public function getSupportedInputs() 
        { return array( 'DOM_XML' ); }

}

/** Register this module with the system. */
FeedMagick2::registerModule('DOMPassthrough');
