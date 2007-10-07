<?php
/**
 * Fetcher
 *
 * Fetch data via URL.
 *
 * @package FeedMagick2
 * @subpackage PipeModules
 * @author l.m.orchard@pobox.com
 * @version 0.1
 *
 * @todo Honor more HTTP caching mechanics.
 * @todo Do some local disk-based caching.
 */
class Fetcher extends FeedMagick2_BasePipeModule {

    /** 
     * Fetch HTTP content at the URL given in parameters.
     * @todo Implement some HTTP-aware caching here.
     * @todo support headers from CLI
     * @todo support headers from POST
     */
    public function fetchOutput_Raw() {
        list($headers, $body) = array(array(), '');

        $method = $_SERVER['REQUEST_METHOD'];

        if ($method=='CLI') {
            if ($_GET['stdin']) {
                $headers = array();
                $body    = file_get_contents('php://stdin');
            }
        } elseif ($method=='POST') {
            $headers = array();
            $body    = file_get_contents('php://input');
        }

        if (!$body) {
            $this->log->debug("Fetcher fetching ".  $this->getParameter('url'));

            // Grab the desired data by local file or URL.
            list($headers, $body) = $this->getParent()->fetchFileOrWeb(
                $this->getParent()->getConfig('paths/web', $this->getParent()->base_dir."/www"),
                $this->getParameter('url')
            );
        }

        // Pass along only whitelisted headers.
        $headers_out = array();
        $headers_whitelist = array_merge(
            array( 'content-type' ),
            $this->getParameter('headers_whitelist', array())
        );
        foreach ($headers as $name => $value) {
            if (in_array(strtolower($name), $headers_whitelist)) {
                $headers_out[$name] = $value;
            }
        }

        return array($headers_out, $body);
    }

}
