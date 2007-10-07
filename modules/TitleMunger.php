<?php
/**
 * TitleMunger
 *
 * Performs manipulations on feed entry titles.
 *
 * @package FeedMagick2
 * @subpackage PipeModules
 * @author l.m.orchard@pobox.com
 * @version 0.1
 */
class TitleMunger extends FeedMagick2_SAXBasePipeModule {

    /**
     * List of within-item elements subject to munging.
     */
    var $MUNGE_TAGS = array(
        'title',
        'http://purl.org/rss/1.0/:title',
    );

    /**
     * For tags subject to summarizing, save up all character data 
     * before emitting/queueing up events.
     */
    function data(&$data) {
        // Should the present tag be summarized?
        $tag = $this->getCurrTag();
        $should_summarize = array_search($tag, $this->MUNGE_TAGS);
        
        if ($this->in_item && $should_summarize !== false) {
            // Yes, so just buffer up all CDATA before queuing events.
            $this->appendCDATA($data);
        } else {
            // No, proceed as normal.
            parent::data($data);
        }
    }

    /**
     * For tags subject to summarizing, strip HTML tags and constrain to
     * final space-delimited word of the text that fits within the
     * character count limit.
     */
    function close(&$tag) {
       
        // Should the data for this element be summarized?
        $should_summarize = array_search($tag, $this->MUNGE_TAGS);
        if ($this->in_item && $should_summarize !== false) {

            // Grab all the buffered up CDATA.
            $data = $this->getCDATA();
            
            // Strip any HTML tags from this data.
            $pre  = $this->getParameter('title_prefix', NULL);
            $post = $this->getParameter('title_suffix', NULL);
            $data = ($pre?$pre:'')."$data".($post?$post:'');

            // Finally, queue up an event for this pent-up data.
            $this->queueData($data);
        }

        // Proceed as normal with closing this tag.
        parent::close($tag);
    }

}
