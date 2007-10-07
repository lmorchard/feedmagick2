<?php
/**
 * Tidyer
 * 
 * Use Tidy to attempt to get parseable XML out of tag soup input.
 *
 * @package FeedMagick2
 * @subpackage PipeModules
 * @author l.m.orchard@pobox.com
 * @version 0.1
 */
class Tidyer extends FeedMagick2_BasePipeModule {

    /** 
     * Fetch raw data from input module and run it through HTML Tidy.
     * @return array ($headers, $body) - Raw headers and body data.
     */
    public function fetchOutput_Raw() {
        list($headers, $body) = $this->getInputModule()->fetchOutput_Raw();

        $opts = array(
            'doctype'=>'omit', 
            'add-xml-decl'=>1, 
            'output-xml'=>1,
            'show-errors'=>0, 
            'show-warnings'=>0, 
            'indent'=>1, 
            'indent-spaces'=>4, 
            'force-output'=>1,
            'quiet'=>1, 
            'numeric-entities'=>1
        );
        if ($this->getParameters()) {
            $opts = array_merge($opts, $this->getParameters());
        }
        $body = tidy_repair_string($body, $opts);
        return array($headers, $body);
    }

}
