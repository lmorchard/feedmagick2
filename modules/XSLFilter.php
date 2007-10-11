<?php
/**
 * XSLFilter
 *
 * Use XSL to process feed content.
 *
 * @package FeedMagick2
 * @subpackage PipeModules
 * @author l.m.orchard@pobox.com
 * @version 0.1
 *
 * @todo Need to accept arbitrary parameters for XSL.
 */
class XSLFilter extends FeedMagick2_DOMBasePipeModule {

    public function processDoc($headers, $doc) {
        
        // Instantiate an XSLT processor
        $xslt = new XSLTProcessor();

        // Load up and parse the XSL from URL.
        list($xsl_headers, $xsl_body) = $this->getParent()->fetchFileOrWeb(
            $this->getParent()->getConfig('paths/xsl', $this->getParent()->base_dir."/xsl"),
            ($xsl_url = $this->getParameter('xsl'))
        );
        $xsl_doc = DOMDocument::loadXML($xsl_body);
        $xslt->importStyleSheet($xsl_doc);

        $opts = $this->getParameters();
        foreach ($opts as $param => $value) {
            $xslt->setParameter('', $param, $value);
        }

        // Process the XSL and retrn the modified document.
        // HACK: This kinda sucks, going from DOM to XSL and back to DOM, but seems less flakey.
        $new_xml = $xslt->transformToXML($doc);
        $new_doc = DOMDocument::loadXML($new_xml);
        $this->log->debug("Processed XSL.");
        return array($headers, $new_doc);
    }

}
