<?php
/**
 * BadgerFish JSON
 *
 * Use BadgerFish to turn parsed XML into JSON
 *
 * @inputs DOM
 * @outputs Raw
 * @package FeedMagick2
 * @subpackage PipeModules
 * @author l.m.orchard@pobox.com
 * @version 0.1
 */
class BadgerFishJSON extends FeedMagick2_BasePipeModule {

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
