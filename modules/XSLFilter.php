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

/**
 * Use XSL to process feed content.
 */
class XSLFilter extends FeedMagick2_DOMBasePipeModule {

    public function getVersion()     
        { return '0.0'; }
    public function getTitle()
        { return "XSLFilter"; }
    public function getDescription() 
        { return 'Use XSL to process feed content.'; }
    public function getAuthor()
        { return 'l.m.orchard@pobox.com'; }
    public function getSupportedInputs() 
        { return array( 'DOM_XML' ); }
    public function getExpectedParameters() {
        return array(
            'xsl' => self::PARAM_STRING | self::PARAM_REQUIRED
        );
    }

    /**
     *
     * @todo Need to accept arbitrary parameters for XSL.
     */
    public function processDoc($headers, $doc) {
        // Instantiate an XSLT processor
        $xslt = new XSLTProcessor();

        $xsl_url = $this->getParameter('xsl');
        $this->log->debug("Fetching XSL at $xsl_url...");

        // Load up and parse the XSL from URL.
        $req =& new HTTP_Request($xsl_url);
        $rv = $req->sendRequest();
        $xsl_doc = DOMDocument::loadXML($req->getResponseBody());
        $xslt->importStyleSheet($xsl_doc);

        $opts = $this->getParameters();
        foreach ($opts as $param => $value) {
            $xslt->setParameter('', $param, $value);
        }

        // Process the XSL and retrn the modified document.
        $this->log->debug("Processing XSL...");
        // HACK: This kinda sucks, going from DOM to XSL and back to DOM, but seems less flakey.
        $new_xml = $xslt->transformToXML($doc);
        $new_doc = DOMDocument::loadXML($new_xml);
        $this->log->debug("...done processing XSL.");
        return array($headers, $new_doc);
    }

}

/** Register this module with the system. */
FeedMagick2::registerModule('XSLFilter');
