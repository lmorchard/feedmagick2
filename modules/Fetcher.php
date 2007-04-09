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
require_once 'HTTP/Request.php';

/**
 * Fetches feeds and data via HTTP.
 * @todo Honor more HTTP caching mechanics.
 * @todo Do some local disk-based caching.
 */
class Fetcher extends FeedMagick2_BasePipeModule {

    public function getTitle()
        { return "HTTP Fetch"; }
    public function getVersion()
        { return '0.0'; }
    public function getDescription()
        { return 'Simple module used to fetch data from a URL via HTTP'; }
    public function getAuthor()
        { return 'l.m.orchard@pobox.com'; }

    public function getExpectedParameters() { 
        return array(
            'url' => self::PARAM_STRING | self::PARAM_REQUIRED,
            'headers_whitelist' => 0 // TODO: Need to define these constants.
        ); 
    }

    /** 
     * Fetch HTTP content at the URL given in parameters.
     * @todo Implement some HTTP-aware caching here.
     */
    public function fetchOutput_Raw() {
        $url = $this->getParameter('url');
        $headers = array();

        if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {

            // If the URL starts with http:// or https://, do a web fetch.
            $this->log->debug("Fetching via HTTP: $url");
            $req =& new HTTP_Request($url);
            $rv = $req->sendRequest();
            $data = $req->getResponseBody();

            // Use the 
            $headers_whitelist = array_merge(
                array( 'content-type' ),
                $this->getParameter('headers_whitelist', array())
            );
            foreach ($req->getResponseHeader() as $name => $value) {
                if (in_array(strtolower($name), $headers_whitelist)) {
                    $headers[$name] = $value;
                }
            }

        } else {
            // Otherwise, treat this as a path to a local file
            $this->log->debug("Fetching via local file: $url");
            $path = $this->getParent()->getConfig('fetch_path', '.');
            $data = file_get_contents("$path/$url");
        }

        return array($headers, $data);
    }

}

/** Register this module with the system. */
FeedMagick2::registerModule('Fetcher');
