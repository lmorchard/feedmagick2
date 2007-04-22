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

class FlickrFavoritesFeed extends FeedMagick2_DOMBasePipeModule {

    public function getVersion()     
        { return "0.0"; }
    public function getTitle()
        { return "Flickr Favorites Feed"; }
    public function getDescription() 
        { return "Build an RSS feed for a user's Flickr favorites"; }
    public function getAuthor()
        { return "l.m.orchard@pobox.com"; }
    public function getExpectedParameters() {
        return array(
            'user' => array('type'=>'string')
        );
    }

    public function __construct($parent, $id=NULL, $options=array()) {
        parent::__construct($parent, $id, $options);

        $this->key = 
            $this->getParameter('key', "e7c2733fc6b236fc47c525ba07b3c332");
        $this->user =
            $this->getParameter('user', 'deusx');
        $this->rest_base_url =
            $this->getParameter('rest_base_url', 'http://flickr.com/services/rest/');
    }

    function fetchOutput_DOM_XML() {

        // Build the initial RSS document.
        $doc = new DOMDocument();
        $doc->formatOutput = TRUE;
        $rss  = $this->append($doc, 'rss', array( 'version'  => '2.0' ));
        $chan = $this->append($rss, 'channel');

        // Set the title and link elements of the feed.
        $this->append($chan, 'title', NULL, $this->user."'s Flickr Favorites");
        $this->append($chan, 'link',  NULL, 'http://flickr.com/photos/'.$this->user.'/favorites/');

        // Look up the user's ID
        list($user, $user_xp) = $this->api(array(
            'method'   => 'flickr.people.findByUsername',
            'username' => $this->user
        ));
        $user_id = $user_xp->query('//user/@id')->item(0)->nodeValue;

        // Request a list of the user's favorite pictures, including the date.
        list($faves, $faves_xp) = $this->api(array(
            'method'  => 'flickr.favorites.getPublicList',
            'extras'  => 'date_taken,date_upload,last_update,owner_name,license',
            'user_id' => $user_id
        ));

        // Process the favorite photos...
        $photo_nodes = $faves_xp->query('//photo');
        foreach ($photo_nodes as $photo_node) {

            // Extract some attributes from the photo as an array.
            $photo = array();
            foreach (array('id', 'server', 'farm', 'secret', 'owner', 'ownername', 
                    'title', 'dateupload', 'lastupdate', 'license') as $name) {
                $photo[$name] = $photo_node->getAttribute($name);
            }

            // Build a link to view the photo.
            $photo['link'] = "http://www.flickr.com/photos/".$photo['owner']."/".$photo['id'];

            // Build the URL to the medium image version of the photo.
            $img_base = "http://farm".$photo['farm'].".static.flickr.com/".
                $photo['server']."/".$photo['id']."_".$photo['secret'];
            $photo['medium_url'] = $img_base."_m.jpg";

            // Add an RSS item for this photo.
            $item = $this->append($chan, 'item');

            // Build the item title from owner name and photo title.
            $this->append($item, 'title', NULL, 
                $photo['ownername']." - ".$photo['title']);

            // Add the photo link to the item.
            $this->append($item, 'link', NULL, $photo['link']);

            // Come up with a pubDate for this photo based on upload time.
            $this->append($item, 'pubDate', NULL, 
                date('r', $photo['dateupload']));

            // Build a quick description for the photo item.
            $this->append($item, 'description', NULL, 
                "<a href=\"{$photo['link']}\"><img src=\"{$photo['medium_url']}\" /></a>");

        }

        $headers = array( 'Content-Type' => 'application/rss+xml' );

        return array($headers, $doc);
    }

    /**
     * Mini unauth'd wrapper for Flickr API.
     * @param array List of parameters to send to Flickr API
     * @return array DOMDocument and DOMXpath of API response
     */
    function api($params) {

        // The API key needs to be in every request, so shove it in here.
        $params['api_key'] = $this->key;

        // Paste the API request URL together.
        $parts = array();
        foreach ($params as $name=>$value) {
            $parts[] = urlencode($name).'='.urlencode($value);
        }
        $url = $this->rest_base_url.'?'.implode('&', $parts);

        // Fire off the HTTP API request.
        $req     =& new HTTP_Request($url);
        $rv      = $req->sendRequest();
        $headers = $req->getResponseHeader();
        $body    = $req->getResponseBody();

        // Parse the response document and build an XPath object.
        $doc = new DOMDocument();
        $doc->loadXML($body);
        $xpath = new DOMXPath($doc);
        return array($doc, $xpath);

    }

}

/** Register this module with the system. */
FeedMagick2::registerModule('FlickrFavoritesFeed');
