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
require_once 'BadgerFish.php';

/**
 * Use BadgerFish to turn parsed XML into JSON
 */
class BadgerFishJSON extends FeedMagick2_BasePipeModule {

    public function getVersion()     
        { return '0.0'; }
    public function getTitle()
        { return "BadgerFish JSON"; }
    public function getDescription() 
        { return 'Use BadgerFish to turn parsed XML into JSON'; }
    public function getAuthor()
        { return 'l.m.orchard@pobox.com'; }
    public function getSupportedInputs() 
        { return array( 'DOM_XML' ); }
    public function getSupportedOutputs() 
        { return array( 'Raw' ); }
    public function getExpectedParameters() 
        { return array(); }

    public function fetchOutput_SAX_XML() {
        die("Module supports raw output only, not SAX_XML.");
    }

    function fetchOutput_DOM_XML() {
        die("Module supports raw output only, not DOM_XML.");
    }
    
    /**
     * @todo Whitelist the callback character set in BasePipeModule with an option slot modifier.
     */
    public function fetchOutput_Raw() {
        list($headers, $doc) = $this->getInputModule()->fetchOutput_DOM_XML();
        $out = BadgerFish::encode($doc);        
        if ($cb = $this->getParameter('callback')) {
            $out = "$cb($out)";
        }
        $headers['Content-Type'] = 'text/javascript';
        return array($headers, $out);
    }

}

/** Register this module with the system. */
FeedMagick2::registerModule('BadgerFishJSON');
