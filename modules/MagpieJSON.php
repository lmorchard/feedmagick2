<?php
/**
 * Magpie JSON
 *
 * Use Magpie to process feed content and return the resulting data as JSON.
 *
 * @package FeedMagick2
 * @subpackage PipeModules
 * @author l.m.orchard@pobox.com
 * @version 0.1
 */
define('MAGPIE_DIR', dirname(__FILE__).'/../extlib/magpierss/');
require_once 'magpierss/rss_parse.inc';

class MagpieJSON extends FeedMagick2_BasePipeModule {
    
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
