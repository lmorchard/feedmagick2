<?php
/**
 * @package FeedMagick2
 * @subpackage PipeModules
 * @author l.m.orchard@pobox.com
 * @version 0.1
 */

/** */
require_once 'FeedMagick2.php';
require_once 'FeedMagick2/SAXBasePipeModule.php';

/**
 * A module that uses SAX to parse and pass feeds through unchanged.
 */
class SAXPassthrough extends FeedMagick2_SAXBasePipeModule {

    public function getVersion()     
        { return '0.0'; }
    public function getTitle()
        { return "SAX Passthrough"; }
    public function getDescription() 
        { return 'Passthrough of SAX parsed XML.'; }
    public function getAuthor()
        { return 'l.m.orchard@pobox.com'; }
    public function getSupportedInputs() 
        { return array( 'SAX_XML' ); }

}

/** Register this module with the system. */
FeedMagick2::registerModule('SAXPassthrough');
