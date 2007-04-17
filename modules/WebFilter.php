<?php
/**
 * @package FeedMagick2
 * @subpackage PipeModules
 * @author l.m.orchard@pobox.com
 * @version 0.1
 */

/** */
require_once 'HTTP/Request.php';
require_once 'FeedMagick2.php';
require_once 'FeedMagick2/BasePipeModule.php';

/**
 * A module that sends incoming raw data to a URL via POST request body and 
 * passes along the response body.
 */
class WebFilter extends FeedMagick2_BasePipeModule {

    public function getVersion()     
        { return '0.0'; }
    public function getTitle()
        { return "Web Filter"; }
    public function getDescription() 
        { return 'A module that sends incoming raw data to a URL via POST request body and passes along the response body.'; }
    public function getAuthor()
        { return 'l.m.orchard@pobox.com'; }
    public function getSupportedInputs() 
        { return array( 'Raw' ); }
    public function getExpectedParameters() { 
        return array(
            'url' => self::PARAM_STRING | self::PARAM_REQUIRED,
            'headers_in_whitelist' => 0,
            'headers_out_whitelist' => 0 // TODO: Need to define these constants.
        ); 
    }

    /** 
     * Fetch headers and body, POST to a URL, send along response headers and body.
     * @return array ($headers, $body) - Raw headers and body data.
     */
    public function fetchOutput_Raw() {
        list($headers, $body) = $this->getInputModule()->fetchOutput_Raw();

        $url = $this->getParameter('url');

        // Set up the request for HTTP POST, including the body.
        $req =& new HTTP_Request($url);
        $req->setMethod('POST');
        $req->setBody($body);

        // Send whitelisted headers with request.
        $headers_in_whitelist = array_merge(
            array( 'content-type' ),
            $this->getParameter('headers_in_whitelist', array())
        );
        foreach ($headers as $name => $value) {
            if (in_array(strtolower($name), $headers_in_whitelist))
                $req->addHeader($name, $value);
        }

        // Fire off the request, get the headers and body.
        $rv      = $req->sendRequest();
        $headers = $req->getResponseHeader();
        $body    = $req->getResponseBody();

        // Pass along only whitelisted headers.
        $headers_out = array();
        $headers_out_whitelist = array_merge(
            array( 'content-type' ),
            $this->getParameter('headers_out_whitelist', array())
        );
        foreach ($headers as $name => $value) {
            if (in_array(strtolower($name), $headers_out_whitelist))
                $headers_out[$name] = $value;
        }

        return array($headers_out, $body);
    }

}

/** Register this module with the system. */
FeedMagick2::registerModule('WebFilter');
