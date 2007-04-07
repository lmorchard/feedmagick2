<?php
/**
 * @package FeedMagick2
 * @author l.m.orchard@pobox.com
 * @version 0.1
 */

/** */
require_once 'FeedMagick2.php';
require_once 'FeedMagick2/BasePipeModule.php';
require_once 'XML/SaxFilters.php';
require_once 'XML/SaxFilters/IO/StringWriter.php';
require_once 'XML/SaxFilters/IO/StringReader.php';
require_once 'FeedMagick2/XMLGeneratorFilter.php';

/**
 * FeedMagick_SaxFeedFilter implements a SAX filter for multiple
 * syndication feed formats (ie. Atom, RSS 1.0, RSS 2.0).  
 *
 * The main purpose is to filter through parsing events and detect when
 * feed items are encountered.  
 *
 * When the start of a feed item is found, all further parsing events are
 * buffered until the end of the item has been found.  At this point, the
 * buffered events can either then be released and sent down the filter
 * chain, or discarded and never seen by the rest of the chain.  
 *
 * This decision depends upon the value of the member variable flag
 * $allow_item.  Subclasses can manipulate this flag in methods such as
 * startItem(), open(), close(), and endItem().
 *
 * Finally, if $allow_item is set to false after endItem() has been called,
 * the buffered item will be tossed out.  If $allow_item is true after
 * endItem() is called, the item will be passed along.
 *
 * Additionally, subclasses can manipulate the content of the buffered
 * item by altering the contents of the member array $item_events.
 *
 * This variable contains the buffer of all events received in the course
 * of parsing the current item.  So, in a subclass implementation, the
 * endItem() method could insert new events into this buffer or delete
 * events in order to alter the final representation of the feed item.
 *
 */
class FeedMagick2_SAXBasePipeModule extends FeedMagick2_BasePipeModule {
    // Should also be an XML_SaxFilters_AbstractFilter

    public function getVersion()     
        { return '0.0'; }
    public function getTitle()
        { return "SAX Base Filter"; }
    public function getDescription() 
        { return 'A base class for SAX-based pipe modules'; }
    public function getAuthor()
        { return 'l.m.orchard@pobox.com'; }
    public function getSupportedInputs() 
        { return array( 'SAX_XML' ); }

    /**
     * List of tags in various feed formats which contain feed items.
     * @var array
     * @access public
     */
    var $ITEM_CONTAINER_TAGS = array(
        'item',
        'entry',
        'http://purl.org/rss/1.0/:item',
        'http://purl.org/atom/ns#:entry',
        'http://www.w3.org/2005/Atom:entry'
    );

    /**
     * Stores the listener
     * @var object (default = NULL) subclass of AbstractFilter
     * @access private
     */
    var $child = NULL;

    /**
     * Stores the parent listener is filtering recursively
     * @var object class or subclass of AbstractFilter
     * @access private
     */
    var $parent;

    /**
     * Stores the writer object
     * @see XML_SaxFilters_IO_WriterInterface
     * @var object class implementing WriterInterface
     * @access public
     */
    var $writer;

    /**
     * Flag indicating if the parser's currently processing an item
     * @var boolean
     * @access public
     */
    var $in_item        = false;
    /**
     * Flag indicating if the current item will be included in the feed
     * @var boolean
     * @access public
     */
    var $allow_item     = true;
    /**
     * Stack of current tag depth
     * @var array
     * @access public
     */
    var $tag_stack      = array();
    /**
     * Stack of current character data depth
     * @var array
     * @access public
     */
    var $data_stack     = array();
    /**
     * Buffered parsing events for the current item being processed
     * @var array
     * @access public
     */
    var $item_events    = array();
    /**
     * Array of select data items parsed from feed item
     * @var array
     * @access public
     */
    var $item_data      = array();

    /**
     * Hook this object up in-line with other SAX filters
     * @return array ($headers, $sax_filter) - Raw headers and this object as a sax filter.
     */
    function fetchOutput_SAX_XML() {
        list($headers, $sax_filter) = $this->getInputModule()->fetchOutput_SAX_XML();
        $this->parser = $sax_filter->parser;
        $sax_filter->setChild($this);
        return array($headers, $this);
    }
    
    /** 
     * Simply fetch and pass through raw data from input module.
     * @return array ($headers, $body) - Raw headers and body data.
     */
    public function fetchOutput_Raw() {
        list($headers, $sax_filter) = $this->fetchOutput_SAX_XML();

        // Create the XML writing end filter.
        $gen_filter = & new FeedMagick2_XMLGeneratorFilter();
        $writer = & new XML_SaxFilters_IO_StringWriter();
        $gen_filter->setWriter($writer);
        $this->setChild($gen_filter);
        
        // Fire up the parser chain!
        $out = array();
        if ( ! $sax_filter->parser->parse() ) {
            $error = $sax_filter->parser->getError();
            array_push($out, $error->getMessage());
        } else {
            $reader = $writer->getReader();
            while (! $reader->isFinal() ) {
                array_push($out, $reader->read());
            }
        }
        
        return array($headers, join('', $out));
    }

    /**
     * Handle feed item start.
     * @return void
     * @access protected
     */
    function startItem() {
        // Assume item inclusion in output allowed.
        $this->allow_item = true;
    }

    /**
     * Handle feed item end.
     * @return void
     * @access protected
     */
    function endItem() {
        // No-op
    }

    /**
     * Handle feed parsing start, initialize state.
     * @return void
     * @access protected
     */
    function startDoc() {
        // Reset parser state.
        $this->in_item     = false;
        $this->allow_item  = false;
        $this->item_events = array();

        // Pass the startDoc event down the parser chain.
        if (isset($this->child)) { 
            $this->child->startDoc(); 
        }
    }

    /**
     * Handle element open.
     * @param string element tag
     * @param array element attributes
     * @return void
     * @access protected
     */
    function open(&$tag, &$attribs) {
        // Parse out the tag namespace UI and tag name
        list($ns_uri, $ns_tag) = $this->splitNS($tag);
        
        // Push the tag data and an empty char data buffer onto stacks.
        array_push($this->tag_stack, array($tag, $attribs));
        array_push($this->data_stack, '');

        // Does the current tag match the list of item container tags?
        if (array_search($tag, $this->ITEM_CONTAINER_TAGS) !== false) {
            // Start of a feed item, so reset item processing state.
            $this->in_item     = true;
            $this->item_events = array();
            $this->item_data   = array();

            // Signal item start to filter subclasses.
            $this->startItem();
        }

        if (!$this->in_item) {
            // If not within a feed item, pass event down parser chain
            if (isset($this->child)) { 
                $this->child->open($tag,$attribs); 
            }
        } else {
            // If within a feed item, buffer parsing event.
            $this->queueOpen($tag, $attribs);
        }
    }

    /**
     * Handle element character data.
     * @param string character data
     * @return void
     * @access protected
     */
    function data(&$data) {
        // Append char data to buffer at the top of the stack.
        $this->appendCDATA($data);

        if (!$this->in_item) {
            // If not within a feed item, pass event down parser chain
            if (isset($this->child)) { 
                $this->child->data($data); 
            }
        } else {
            // If within a feed item, buffer parsing event.
            $this->queueData($data);
        }
    }
    
    /**
     * Handle element close.
     * @param string element tag
     * @return void
     * @access protected
     */
    function close(&$tag) {
        // Parse out the tag namespace UI, name, and attribs
        list($ns_uri, $ns_tag) = $this->splitNS($tag);
        $attribs = $this->getAttributes();

        if (!$this->in_item) {
            // If not processing an item, pass the event down chain.
            if (isset($this->child)) { 
                $this->child->close($tag); 
            }
        } else {
            // Buffer the item parsing event.
            $this->queueClose($tag);
        }

        // Does the current tag match the list of item container tags?
        if (array_search($tag, $this->ITEM_CONTAINER_TAGS) !== false) {
            
            // Signal end of feed item to subclasses.
            $this->endItem();
            
            // Is item allowed into the feed?  If so, emit buffered items.
            if ($this->allow_item) { 
                $this->emitItemEvents(); 
            }
            
            // Flag no longer processing an item.
            $this->in_item = false;
        }

        // At close of tag, pop processing state
        array_pop($this->tag_stack);
        array_pop($this->data_stack);
    }

    /**
     * Handle feed document parsing end
     * @return void
     * @access protected
     */
    function endDoc() {
        if (isset($this->child)) { 
            $this->child->endDoc(); 
        }
    }

    /**
     * Queue up an <code>open</code> parsing event.
     * @return void
     */
    function queueOpen(&$tag, &$attribs) {
        array_push($this->item_events, 
            array('open', array($tag, $attribs)));
    }

    /**
     * Queue up a <code>close</code> parsing event.
     * @return void
     */
    function queueClose(&$tag) {
        array_push($this->item_events, array('close', $tag));
    }

    /**
     * Queue up a <code>data</code> parsing event.
     * @return void
     */
    function queueData(&$data) {
        array_push($this->item_events, array('data', $data));
    }

    /**
     * Fire off all buffered feed item events to filter downstream
     * @return void
     * @access protected
     */
    function emitItemEvents() {
        if (isset($this->child)) {
            // Process through all the buffered events.
            foreach($this->item_events as $event) {
                list($name, $data) = $event;
                switch($name) {
                    case 'open':  
                        $this->child->open($data[0], $data[1]); break;
                    case 'close': 
                        $this->child->close($data); break;
                    case 'data':  
                        $this->child->data($data); break;
                }
            }
        }
        // Empty the event buffer.
        $this->item_events = array();
    }

    /**
     * Handle splitting up NS uri and name for tags
     * @param string parsed tag string
     * @return array containing URI and name of tag
     * @access protected
     */
    function splitNS($str) {
        if (strpos($str,':') === false) {
            return array('', $str); 
        } else {
            $pos    = strrpos($str,':');
            $ns_uri = substr($str,0,$pos);
            $ns_str = substr($str,$pos+1);
            return array($ns_uri, $ns_str);
        }
    }
    
    function getCDATA() {
        return $this->data_stack[count($this->data_stack)-1];
    }
    
    function appendCDATA(&$data) {
        $this->data_stack[count($this->data_stack)-1] .= $data;
    }

    function getAttributes() {
        list(,$attribs) = $this->tag_stack[count($this->tag_stack)-1];
        return $attribs;
    }

    function getCurrTag() {
        list($tag,$attribs) = $this->tag_stack[count($this->tag_stack)-1];
        return $tag;
    }
    
    /**
     * Sets the child filter to which events are delegated
     * @param object class or subclass of AbstractFilter
     * @return void
     * @access public
     */
    function setChild(& $child) {
        $this->child=& $child;
    }

    /**
     * Unsets the child filter
     * @return void
     * @access public
     */
    function unsetChild() {
        $this->child->__destroy();
        unset($this->child);
    }

    /**
     * Sets the parent filter allow the child to talk back
     * @param object class or subclass of AbstractFilter
     * @return void
     * @access public
     */
    function setParent(& $parent) {
        $this->parent=& $parent;
    }

    /**
     * Breaks the connection to from the child to the parent filter.
     * @return void
     * @access public
     */
    function unsetParent() {
        $this->parent->__destroy();
        unset($this->parent);
    }

    /**
     * Calls the parent setChild() method allow a child filter
     * to set another child filter in the parent
     * @param object class or subclass of AbstractFilter
     * @return void
     * @access public
     */
    function attachToParent (& $child) {
        $this->parent->setChild($child);
    }

    /**
     * Calls the parent unsetChild() method removing any child filter
     * from the parent
     * @return void
     * @access public
     */
    function detachFromParent () {
        $this->parent->unsetChild();
    }

    /**
     * Sets the writer
     * @param object class implementing WriterInterface
     * @return void
     * @access public
     */
    function setWriter(& $writer) {
        $this->writer=& $writer;
    }

    /**
     * Unsets the writer
     * @return object class implementing WriterInterface
     * @access public
     */
    function & getWriter() {
        return $this->writer;
    }

    /**
     * Called whenever a filter is unset. Provides a clean up state where
     * a filter can check parsing for errors
     * @return void
     * @access public
     */
    function __destroy() {}
    
    /**
     * Sax start namepace handler
     * @param string namespace name
     * @param array namespace URI
     * @return void
     * @access public
     */
    function startNS(& $qname,& $uri) {
        if ( isset($this->child) ) {
            $this->child->startNS($qname,$uri);
        }
    }
    
    /**
     * Sax end namespace handler
     * @param string namespace name
     * @param array namespace URI
     * @return void
     * @access public
     */
    function endNS(& $qname,& $uri) {
        if ( isset($this->child) ) {
            $this->child->endNS($qname,$uri);
        }
    }

    /**
     * Sax processing instruction handler
     * @param string target processor
     * @param string instruction for processor
     * @return void
     * @access protected
     */
    function pi(& $target, & $instruction) {
        if ( isset($this->child) ) {
            $this->child->pi($target,$instruction);
        }
    }
    
    /**
     * Escape handler (PEAR::XML_HTMLSax only)
     * @param string contents of escape
     * @return void
     * @access protected
     */
    function escape(& $data) {
        if ( isset($this->child) ) {
            $this->child->escape($data);
        }
    }
    
    /**
     * JSP/ASP markup handler (PEAR::XML_HTMLSax only)
     * @param string contents of escape
     * @return void
     * @access protected
     */
    function jasp(& $data) {
        if ( isset($this->child) ) {
            $this->child->jasp($data);
        }
    }
    
}

?>
