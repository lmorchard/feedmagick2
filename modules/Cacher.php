<?php
/**
 * Cacher
 *
 * Cache the output of the input feed, short-circuit input module execution.
 *
 * @package FeedMagick2
 * @subpackage PipeModules
 * @author l.m.orchard@pobox.com
 * @version 0.1
 */
class Cacher extends FeedMagick2_BasePipeModule {

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
