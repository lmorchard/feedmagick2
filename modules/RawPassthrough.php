<?php
/**
 * Raw Passthrough
 *
 * An example module that passes raw content through.
 *
 * @package FeedMagick2
 * @subpackage PipeModules
 * @author l.m.orchard@pobox.com
 * @version 0.1
 */
class RawPassthrough extends FeedMagick2_BasePipeModule {
    /** 
     * Simply fetch and pass through raw data from input module.
     * @return array ($headers, $body) - Raw headers and body data.
     */
    public function fetchOutput_Raw() {
        list($headers, $body) = $this->getInputModule()->fetchOutput_Raw();
        return array($headers, $body);
    }

}
