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
require_once 'Cache/Lite.php';

/**
 * A module that passes raw content through unchanged.
 */
class Cacher extends FeedMagick2_BasePipeModule {

    public function getVersion()     
        { return '0.0'; }
    public function getTitle()
        { return "Cacher"; }
    public function getDescription() 
        { return 'Cache the output of the input feed, shortcircuit input module execution.'; }
    public function getAuthor()
        { return 'l.m.orchard@pobox.com'; }
    public function getSupportedInputs() 
        { return array( 'Raw' ); }

    /**
     * Use characteristics of the input module and the request itself to calculate a cache key.
     * @todo Find a better way to calculate cache key
     * @todo Use cache grouping?
     */
    public function calculateCacheId() {
        $input = $this->getInputModule();
        return md5(
            "Cacher-".
            $_SERVER['REQUEST_URI']."\n".
            $input->getId()."\n".
            var_export($input->getParameters(), TRUE)
        );
    }

    /** 
     * Simply fetch and pass through raw data from input module.
     * @return array ($headers, $body) - Raw headers and body data.
     */
    public function fetchOutput_Raw() {
        // Layer defaults with parent config with module config for Cache_Lite
        $cache = new Cache_Lite(array_merge(
            array('cacheDir' => './data/cache/', 'lifeTime' => '3600'),
            $this->getParent()->getConfig('cache', array()),
            $this->getParameters()
        ));

        $key = $this->calculateCacheId();
        if ($data = $cache->get($key)) {
            $this->log->debug("Cache HIT for $key");
            $out = unserialize($data); 
        } else {
            $this->log->debug("Cache MISS for $key");
            $out = $this->getInputModule()->fetchOutput_Raw();
            $rv = $cache->save(serialize($out), $key);
        }
        return $out;
    }

}

/** Register this module with the system. */
FeedMagick2::registerModule('Cacher');
