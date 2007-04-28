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
 * Use HTML Tidy to attempt to get parseable XML out of tag soup input.
 */
class Tidyer extends FeedMagick2_BasePipeModule {

    public function getVersion()     
        { return '0.0'; }
    public function getTitle()
        { return "Tidyer"; }
    public function getDescription() 
        { return 'Use Tidy to attempt to get parseable XML out of tag soup input.'; }
    public function getAuthor()
        { return 'l.m.orchard@pobox.com'; }
    public function getSupportedInputs() 
        { return array( 'Raw' ); }

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
            'output-xhtml'=>1,
            'show-errors'=>0, 
            'show-warnings'=>0, 
            'indent'=>1, 
            'indent-spaces'=>4, 
            'wrap'=>96, 
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

/** Register this module with the system. */
FeedMagick2::registerModule('Tidyer');
