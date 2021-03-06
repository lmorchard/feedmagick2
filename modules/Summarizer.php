<?php
/**
 * Summarizer
 *
 * Enforce a character limit on item content / summary / description / title
 * and perform some entity replacements.
 *
 * @package FeedMagick2
 * @subpackage PipeModules
 * @author l.m.orchard@pobox.com
 * @version 0.1
 */
class Summarizer extends FeedMagick2_SAXBasePipeModule {

    /**
     * List of within-item elements subject to summarizing.
     */
    var $SUMMARIZE_TAGS = array(
        'description',
        'http://purl.org/rss/1.0/:description',
        'title',
        'http://purl.org/rss/1.0/:title',
        'summary',
        'http://purl.org/atom/ns#:summary',
        'http://www.w3.org/2005/Atom:summary',
        'content',
        'http://purl.org/atom/ns#:content',
        'http://www.w3.org/2005/Atom:content',
        'content:encoded',
        'http://purl.org/rss/1.0/modules/content/:encoded'
    );

    var $ENTITY_REPLACEMENTS = array(
        '&ldquo;'  => '"',
        '&rdquo;'  => '"',
        '&reg;'    => '(R)',
        '&#174;'   => '(R)',
    );

    /**
     * For tags subject to summarizing, save up all character data 
     * before emitting/queueing up events.
     */
    function data(&$data) {
        // Should the present tag be summarized?
        $tag = $this->getCurrTag();
        $should_summarize = array_search($tag, $this->SUMMARIZE_TAGS);
        
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
        $should_summarize = array_search($tag, $this->SUMMARIZE_TAGS);
        if ($this->in_item && $should_summarize !== false) {

            // Grab all the buffered up CDATA.
            $data = $this->getCDATA();
            
            // Strip any HTML tags from this data.
            $data = strip_tags($data);

            // Replace arbitrary entities with "safer" characters
            foreach ($this->ENTITY_REPLACEMENTS as $entity=>$replacement) {
                $data = str_replace($entity, $replacement, $data);
            }
            
            // Determine the actual length and constrained length for the text.
            $actual_len = strlen($data);
            $limit_len  = $this->getParameter('char_limit', 150);
            $final_len  = min($actual_len, $limit_len);
            
            if ($final_len < $actual_len) {
                
                // Constrain the string length.
                $data = substr($data, 0, $final_len);
                
                // Snip a bit at the end so that it finishes on a whole word.
                $data = substr($data, 0, strrpos($data, ' '))."...";
            }
            
            // Finally, queue up an event for this pent-up data.
            $this->queueData($data);
        }

        // Proceed as normal with closing this tag.
        parent::close($tag);
    }
    
}
