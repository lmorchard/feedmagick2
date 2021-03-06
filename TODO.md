## TODO

This is a list of TODOs for this package.  At the end of the file, TODOs are
auto-extracted from source using bin/update-todo.sh

### Bugs

* Since root dir is web dir, need to protect all bare PHP includes from execution

### v0.5 Release

* Blog post
* HTTP cache control on Fetcher
* More tests
* Modules
    * Starter / Blank
    * DateFirstSeen
    * FeedHeaderMunger
    * ReadingLister
    * Inbox
    * Stretcher
    * From / To hAtom
    * ImportedPipeline / ExternalPipeline

### v1.0 Release

* Builder
* Docs
* All tests

### Improvements

* Completely separate pipeline runner from web UI.

* Make inspector form work with remote pipeline URL

* Need to better explore / think out request / response headers in the pipeline.
* Enforce required parameters in pipeline definition, and in URL params
* Config options to lock down some open features for semi-private usage
    * No usage pages
    * No URL-based pipelines
    * No POST-based feed input
* Use Services_JSON only when json_encode / json_decode not available?
* Cacher / Fetcher
    * Obey and produce HTTP caching semantics
    * Store and use ETag and Last-Modified in Fetcher
* Options for Fetcher / XSLFilter / Pipeline to pull only from local files.
* Pipeline parameters
    * Spec
        * id
        * type
        * required
        * default
        * label
    * Types
        * string
        * url
        * url-encoded
        * int
        * boolean
        * enum
        * json / user defined

### Wishlist

* Builder
    * An AJAXified builder app that uses module metadata to piece togther pipelines.
* Modules
    * Google JSON clone
        * http://code.google.com/apis/ajaxfeeds/documentation/
    * ReadingLister
        * Accept an OPML list of feeds
        * Blend together into one big feed
    * HTTP header munger
        * Alter things like User-Agent sent on request.
    * Feed Header Munger
        * Replace the feed title / link / description / etc with parameters
    * To hAtom / hAtomizer
        * Render any feed as hAtom
    * From hAtom / DehAtomizer
        * Turn hAtom into Atom.
    * Inboxer / Item Inbox
        * Simple stdin / POST interface to add items to a feed, stored locally in small file buffer
           * Good for logging, simple status updates
        * Possibly offer Atom Publishing Protocol compliance
        * Possibly accept posts from mailhook.org
        * Should be passworded / keyed
    * Stretcher
        * Keep a local cache of feed items, keep them in the feed longer than original
        * Good for piling up items from a single entry feed
    * Archiver
        * Keep daily / hourly / etc archive feeds of unique items
        * Side-effect of GET'ing the feed
    * ImportedPipeline / ExternalPipeline
        * Module that refers to another JSON pipeline definition by URL and splices it in.
        * Stick the definition-via-url logic into the pipeline class itself.
            * Allow sub-pipelines to be pulled in via URL as well.
    * Regex Filter
        * Specify a negative/positive regex criteria on CDATA of an arbitrary item element
    * Auto-tagger
        * Auto add dc:subject and/or category tags based on certain patterns
    * Normalize/Repair
        * Use Magpie to liberally parse broken feed, rebuild into working feed
    * Favicon Sprinkler
        * For blended feeds, drop in an img ref to the original site's favicon

### Blue Sky

* I want a pony.

### Extracted

<pre class="todo">
./lib/FeedMagick2/BasePipeModule.php:51:     * @todo Find a more efficient way to do this.
./lib/FeedMagick2/BasePipeModule.php:52:     * @todo Make the slot options (ie. {foo|u}) more modular and sophisticated.
./lib/FeedMagick2/Pipeline.php:14: * @todo Methods to prepend or append pipe modules, post-construction
./lib/FeedMagick2.php:42:     * @todo Try to replace Services_JSON with the PHP binary extension, but not working on my laptop.
./lib/FeedMagick2.php:302:     * @todo Figure out if any file system based headers make sense here. (modified, etc)
./lib/FeedMagick2.php:365:     * @todo Carry headers along as a property of this object?
./lib/FeedMagick2.php:366:     * @todo Need better error handling here.
./lib/HTTP/CachedRequest.php:5: * @todo Allow caching method to be pluggable via callbacks (ie. memory, mysql, etc)
./lib/HTTP/CachedRequest.php:6: * @todo Make cache dir and hash level configurable settings.
./lib/HTTP/CachedRequest.php:7: * @todo Option to specify max age of local cache to shortcircuit HTTP requests.
./lib/HTTP/CachedRequest.php:8: * @todo Honor HTTP cache control headers
./modules/BadgerFishJSON.php:25:     * @todo Whitelist the callback character set in BasePipeModule with an option slot modifier.
./modules/Blender.php:21: * @todo Auto-convert sub-module feeds into format of input feed?
./modules/Cacher.php:16:     * @todo Find a better way to calculate cache key
./modules/Cacher.php:17:     * @todo Use cache grouping?
./modules/Fetcher.php:12: * @todo Honor more HTTP caching mechanics.
./modules/Fetcher.php:13: * @todo Do some local disk-based caching.
./modules/Fetcher.php:19:     * @todo Implement some HTTP-aware caching here.
./modules/Fetcher.php:20:     * @todo support headers from CLI
./modules/Fetcher.php:21:     * @todo support headers from POST
./modules/MagpieJSON.php:18:     * @todo Whitelist the callback character set in BasePipeModule with an option slot modifier.
./modules/SortLimiter.php:17: * @todo This could probably be done with XSL, but implemented this way will work without libxsl.
./modules/SortLimiter.php:18: * @todo Implement some other sort types - ie. for numeric, RSS RFC2822 pubDate
./modules/SortLimiter.php:19: * @todo Date sorting in Atom is buggy.  Fix this.
./modules/SortLimiter.php:20: * @todo Rework so that limiting can be done separately from sorting.
./modules/XPathFilter.php:12: * @todo This could probably be done with XSL, but implemented this way will work without libxsl.
./modules/XSLFilter.php:12: * @todo Need to accept arbitrary parameters for XSL.
./bin/update-todo.sh:2:# TODO: Make this suck less, because it sucks a lot.
./lib/FeedMagick2/XMLGeneratorFilter.php:46:        // TODO: Need to build a proper XML declaration here with respect to encoding, charset
./lib/FeedMagick2/XMLGeneratorFilter.php:70:        // TODO: Not sure about the parameters of namespace end handler.
./modules/ReadingListBlender.php:100:                        // TODO: Copy more metadata from atom feeds?
./modules/ReadingListBlender.php:127:            // TODO: Date sorting in Atom is buggy.  Fix this.
./modules/ReadingListBlender.php:250:        // TODO: Improve this quick and dirty normalizing short-circuit
./xsl/ficlets-extended.xsl:64:                            <!-- TODO: Could find the date, but would need to spider another page level deep -->
./xsl/ficlets-extended.xsl:79:                            <!-- TODO: Could find the date, but would need to spider another page level deep -->
./xsl/normalizer.xsl:82:            <!-- TODO: Handle unescaping escaped entities and such from RSS -->
./xsl/scrapers/lib/atom030.xsl:84:      <!-- TODO: Need an entry modified date, which is a problem without persistent data -->
./xsl/scrapers/lib/atom030.xsl:86:      <!-- TODO: Need an entry issued date, which is a problem without persistent data -->
./xsl/scrapers/lib/atom100.xsl:84:      <!-- TODO: Need an entry modified date, which is a problem without persistent data -->
./xsl/scrapers/lib/atom100.xsl:86:      <!-- TODO: Need an entry issued date, which is a problem without persistent data -->
./xsl/scrapers/lib/escape-html.xsl:43:  <!-- TODO: This double-replace call seems really smelly. -->
</pre>
