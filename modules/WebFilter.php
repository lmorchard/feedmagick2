<?php
/**
 * WebFilter
 *
 * A module that sends incoming raw data to a URL via POST request body and passes along the response body.
 *
 * @package FeedMagick2
 * @subpackage PipeModules
 * @author l.m.orchard@pobox.com
 * @version 0.1
 */
class WebFilter extends FeedMagick2_BasePipeModule {

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
