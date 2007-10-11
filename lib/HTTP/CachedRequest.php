<?php
/**
 * Share and Enjoy.
 *
 * @todo Allow caching method to be pluggable via callbacks (ie. memory, mysql, etc)
 * @todo Make cache dir and hash level configurable settings.
 * @todo Option to specify max age of local cache to shortcircuit HTTP requests.
 * @todo Honor HTTP cache control headers
 */
require_once 'HTTP/Request.php';

/**
 * HTTP client with conditional GET support.
 *
 * @category HTTP
 * @package HTTP_CachedRequest
 * @author l.m.orchard <l.m.orchard@pobox.com>
 */
class HTTP_CachedRequest extends HTTP_Request {

    public static $cache_dir = './data/http-cache';

    public static $cache_max_age = 3600;
   
    public static $cache_hash_level = 4;

    /**
     * Constructor.
     *
     * Sets up the object
     * @access public
     * @param    string  The url to fetch/access
     * @param    array   Associative array of parameters
     */
    function HTTP_CachedRequest($url='', $params=array())
    {
        parent::HTTP_Request($url, $params);
    } 

    /**
     * Sends the request
     *
     * @access public
     * @param bool Whether to store response body in Response object property.
     * @param bool Whether to support conditional GET and 304 cache retrieval.
     * @return mixed  PEAR error on error, true otherwise
     */
    function sendRequest($saveBody=true, $conditionalGet=true, $forceStale=false)
    {
        // Conditional GET is only supported for GET method.
        if ($this->_method != HTTP_REQUEST_METHOD_GET)
            $conditionalGet = false;

        if ($conditionalGet) {

            // Attempt to fetch the data cached for this URL.
            $cached = $this->_cached = $this->getCachedUrl($this->_url);
            if ($cached) {

                // If there's cached data, use it to add conditional GET headers.
                if (isset($cached['headers'])) {
                    $headers = $cached['headers'];
                    if (isset($headers['last-modified'])) {
                        $this->addHeader('If-Modified-Since', $headers['last-modified']);
                    }
                    if (isset($headers['etag'])) {
                        $this->addHeader('If-None-Match', $headers['etag']);
                    }
                }

                // Determine whether this cached data can be considered stale.
                $is_stale = $this->_is_stale = $forceStale || (!$cached['time']) || 
                    ( ( $cached['time'] + self::$cache_max_age) < time() );

            }

        }

        // Skip the request if conditional GET on and cache not stale.
        if ($conditionalGet && $cached && !$is_stale) {
            $this->_skipped_request = true;
        } else {
            parent::sendRequest($saveBody);
            $this->_skipped_request = false;
        }

        // Only support returning cached data for GET method.
        if ($conditionalGet) {

            // If the response code was a 304 and we have cached data, it's a hit.
            if ($cached && ( $this->getResponseCode() == 304 || !$is_stale ) ) {

                $this->_cache_hit = true;

                // Preserve the original headers and cookies.
                $this->_response->_original_headers = $this->_response->_headers;
                $this->_response->_original_cookies = $this->_response->_cookies;

                // Drop in the cached request data.
                $this->_response->_body    = $cached['body'];
                $this->_response->_headers = $cached['headers'];
                $this->_response->_cookies = $cached['cookies'];

                // Since this was a not-modified result, bump up the cache timestamp.
                if ($is_stale) {
                    $cached['time'] = time();
                    $this->putCachedUrl($this->_url, $cached);
                }

            } else {

                $this->_cache_hit = false;

                // This was a cache miss, so stow the current request's data 
                // into the cache.
                $this->putCachedUrl($this->_url, array(
                    'time'    => time(),
                    'code'    => $this->getResponseCode(),
                    'headers' => $this->getResponseHeader(),
                    'body'    => $this->getResponseBody(),
                    'cookies' => $this->getResponseCookies()
                ));

            }

        }

    }

    /**
     * Attempt to fetch cached data for the given URL.
     * 
     * @param Net_URL
     * @return mixed
     */
    function getCachedUrl($url)
    {
        $fn = $this->_buildUrlCacheFilename($url);
        if (!is_readable($fn)) {
            return FALSE;
        } else {
            return unserialize(file_get_contents($fn));
        }
    }

    /**
     * Attempt to store cached data for the given URL.
     *
     * @param Net_URL
     * @param mixed
     */
    function putCachedUrl($url, $data)
    {
        $fn  = $this->_buildUrlCacheFilename($url);
        $dir = dirname($fn);
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        file_put_contents($fn, serialize($data));
    }

    /**
     * Build a cache file path for the given URL.
     *
     * @param Net_URL
     * @return string
     */
    function _buildUrlCacheFilename($url)
    {
        $path = self::$cache_dir;
        $hash = md5($url->getURL());
        for ($i=0; $i<self::$cache_hash_level; $i++) {
            $path .= '/'.substr($hash, $i, 1);
        }
        $path .= '/'.$hash;
        return $path;
    }
    
}
?>
