<?php
/**
 * @package FeedMagick2
 * @author l.m.orchard <l.m.orchard@pobox.com>
 * @version 0.0
 */
 
/**
* Required classes
*/
require_once 'XML/SaxFilters.php';

/**
 * FeedMagick2_XMLGeneratorFilter implements a SAX filter that reconstitutes
 * parsing events back into XML data sent on the fly into a Writer object.
 *
 * Roughly inspired by xml.sax.saxutils.XMLGenerator in Python:
 * 
 *     http://www.python.org/doc/lib/module-xml.sax.saxutils.html#l2h-4512
 *     http://www-128.ibm.com/developerworks/xml/library/x-tipsaxflex.html
 *
 */
class FeedMagick2_XMLGeneratorFilter extends XML_SaxFilters_AbstractFilter {
    var $curr_ns   = NULL;
    var $output_ns = NULL;
    var $finished_start_tag  = false;

    function xmlentities ($string)  {
        return str_replace ( array ( '&', '"', "'", '<', '>' ), 
            array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;' ), 
            $string );
    }

    /**
     * Handle document processing start, reset filter state.
     * @return void
     * @access protected
     */
    function startDoc() {
        $this->curr_ns   = Array('http://www.w3.org/XML/1998/namespace'=>'xml');
        $this->output_ns = Array('http://www.w3.org/XML/1998/namespace'=>'xml');

        // TODO: Need to build a proper XML declaration here with respect to encoding, charset
        $this->writer->write('<?xml version="1.0" encoding="utf-8"?'.">\n");
    }

    /**
     * Handle the introduction of a new namespace
     * @param string namespace name
     * @param array namespace URI
     * @return void
     * @access public
     */
    function startNS(&$qname,&$uri) {
        // Track new namespace declarations.
        $this->curr_ns[$uri] = $qname;
    }

    /**
     * Handle the ending of a declared namespace
     * @param string namespace name
     * @param array namespace URI
     * @return void
     * @access public
     */
    function endNS(&$qname,&$uri) {
        // TODO: Not sure about the parameters of namespace end handler.
        unset($curr_ns[$uri]); 
        unset($output_ns[$uri]); 
    }

    /**
     * Sax processing instruction handler
     * @param string target processor
     * @param string instruction for processor
     * @return void
     * @access protected
     */
    function pi(&$target, &$instruction) {
        $this->writer->write("<?$target $instruction?".">\n");
    }
    
    /**
     * Handle the start of a new tag.
     * @param string namespaced tag 
     * @param array attributes on tag
     * @return void
     * @access public
     */
    function open(&$tag, &$attribs) {
        list($ns_uri, $ns_name, $ns_tag) = $this->splitNS($tag);

        // Write the start of the tag
        $this->writer->write("<");
        if ($ns_name != '') { 
            $this->writer->write("$ns_name:"); 
        }
        $this->writer->write("$ns_tag");

        // Write namespace declarations's not yet written.
        $ns_to_output = array_diff_assoc($this->curr_ns, $this->output_ns);
        foreach ( $ns_to_output as $uri => $qname) {
            $this->writer->write(' xmlns');
            if ($qname != '') { 
                $this->writer->write(':'.$qname); 
            }
            $this->writer->write('="'.$uri.'"');
            $this->output_ns[$uri] = $qname;
        }

        // Write attributes, being sensitive to namespaces.
        if (count($attribs) > 0) {
            foreach ( $attribs as $key => $value ) {
                list($ns_attr_uri, $ns_attr_ns_name, $ns_attr_name) = 
                    $this->splitNS($key);
                $this->writer->write(" ");
                if ($ns_attr_ns_name != '') { 
                    $this->writer->write("$ns_attr_ns_name:"); 
                }
                $this->writer->write($ns_attr_name.'="'.$this->xmlentities($value).'"');
            }
        }

        // Defer finishing the tag until soem data arrives.
        $this->finished_start_tag = false;
    }

    /**
     * Handle charcter data
     * @param string character data
     * @return void
     * @access public
     */
    function data(&$data) {
        if (!$this->finished_start_tag) {
            // If this is the first bit of data to arrive, finish the 
            // opening tag first.
            $this->finished_start_tag = true;
            $this->writer->write(">");
        }
        $this->writer->write($this->xmlentities($data));
    }
    
    /**
     * Handle the end of an open tag.
     * @param string namespaced tag 
     * @return void
     * @access public
     */
    function close(&$tag) {
        if (!$this->finished_start_tag) {
            // If no character data arrived, just terminate 
            // the opening tag.
            $this->writer->write(" />");
            $this->finished_start_tag = true;
        } else {
            // Handle writing out the namespaced closing tag.
            list($ns_uri, $ns_name, $ns_tag) = $this->splitNS($tag);
            $this->writer->write("</");
            if ($ns_name != '') { 
                $this->writer->write("$ns_name:"); 
            }
            $this->writer->write("$ns_tag>");
        }
    }

    /**
     * Utility function to handle namespaced tag details.
     * @param string namespaced tag 
     * @return list containing the NS URI, NS name, and the local tag name.
     * @access private
     */
    function splitNS($str) {
        if (strpos($str,':') === false) {
            return array('', '', $str); 
        } else {
            $pos    = strrpos($str,':');
            $ns_uri = substr($str,0,$pos);
            $ns_str = substr($str,$pos+1);
            if (array_key_exists($ns_uri, $this->curr_ns)) {
                $ns_name = $this->curr_ns[$ns_uri];
            } else {
                $ns_name = $ns_uri;
            }
            return array($ns_uri, $ns_name, $ns_str);
        }
    }

}
