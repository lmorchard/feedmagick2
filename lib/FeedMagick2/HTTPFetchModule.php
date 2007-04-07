<?php
/**
 *
 *
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
 *
 */
class FeedMagick2_HTTPFetchModule extends FeedMagick2_BasePipeModule {

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
            'url' => self::PARAM_STRING | self::PARAM_REQUIRED
        ); 
    }

    /** Construct an instance of the pipe module. */
    public function __construct($id=NULL, $options=array()) {
        parent::__construct($id, $options);
    }

    /** 
     * Fetch HTTP content at the URL given in parameters.
     * @todo Implement some HTTP-aware caching here.
     */
    public function fetchOutput_Raw() {
        $req =& new HTTP_Request($this->getParameter('url'));
        $rv = $req->sendRequest();
        $headers = array();
        foreach ($req->getResponseHeader() as $name => $value) {
            $headers[$name] = $value;
        }
        return array($headers, $req->getResponseBody());
    }

}

/** Register this module with the system. */
FeedMagick2::registerModule('FeedMagick2_HTTPFetchModule');
