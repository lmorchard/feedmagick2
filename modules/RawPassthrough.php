<?php
/**
 * @package FeedMagick2
 * @subpackage PipeModules
 * @author l.m.orchard@pobox.com
 * @version 0.1
 */

/** */
require_once 'FeedMagick2.php';
require_once 'FeedMagick2/BasePipeModule.php';

/**
 * A module that passes raw content through unchanged.
 */
class RawPassthrough extends FeedMagick2_BasePipeModule {

    public function getVersion()     
        { return '0.0'; }
    public function getTitle()
        { return "Raw Passthrough"; }
    public function getDescription() 
        { return 'Simple passthrough of raw data.'; }
    public function getAuthor()
        { return 'l.m.orchard@pobox.com'; }
    public function getSupportedInputs() 
        { return array( 'Raw' ); }

    /** 
     * Simply fetch and pass through raw data from input module.
     * @return list($headers, $body) - Raw headers and body data.
     */
    public function fetchOutput_Raw() {
        list($headers, $body) = $this->getInputModule()->fetchOutput_Raw();
        return array($headers, $body);
    }

}

/** Register this module with the system. */
FeedMagick2::registerModule('RawPassthrough');
