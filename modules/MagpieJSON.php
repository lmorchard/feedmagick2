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
require_once 'Services/JSON.php';

define('MAGPIE_DIR', 'extlib/magpierss/');
require_once 'magpierss/rss_parse.inc';

/**
 * Use Magpie to process feed content and return the resulting data as JSON.
 */
class MagpieJSON extends FeedMagick2_BasePipeModule {

    public function getVersion()     
        { return '0.0'; }
    public function getTitle()
        { return "Magpie JSON"; }
    public function getDescription() 
        { return 'Use Magpie to process feed content and return the resulting data as JSON'; }
    public function getAuthor()
        { return 'l.m.orchard@pobox.com'; }
    public function getSupportedInputs() 
        { return array( 'DOM_XML' ); }
    public function getSupportedOutputs() 
        { return array( 'Raw' ); }
    public function getExpectedParameters() 
        { return array(); }
    public function fetchOutput_SAX_XML() 
        { die("Module supports raw output only, not SAX_XML."); }
    public function fetchOutput_DOM_XML() 
        { die("Module supports raw output only, not DOM_XML."); }
    
    /**
     * @todo Whitelist the callback character set in BasePipeModule with an option slot modifier.
     */
    public function fetchOutput_Raw() {
        list($headers, $raw) = $this->getInputModule()->fetchOutput_Raw();
        $rss_data = new MagpieRSS($raw, null, 'UTF-8');
        $rss_out  = array(
            'feed_type'    => $rss_data->feed_type,
            'feed_version' => $rss_data->feed_version,
            'channel'      => $rss_data->channel,
            'items'        => $rss_data->items,
            'textinput'    => $rss_data->textinput,
            'image'        => $rss_data->image
        );
        $json = new Services_JSON();
        $out = $json->encode($rss_out);
        if ($cb = $this->getParameter('callback')) {
            $out = "$cb($out)";
        }
        $headers['Content-Type'] = 'text/javascript';
        return array($headers, $out);
    }

}

/** Register this module with the system. */
FeedMagick2::registerModule('MagpieJSON');
