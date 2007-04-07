<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Luis Argerich <lrargerich@yahoo.com> Original Author        |
// | Authors: Harry Fuecks <hfuecks@phppatterns.com> Port to PEAR + more  |
// +----------------------------------------------------------------------+
//
// $Id: AbstractFilter.php,v 1.5 2003/09/25 17:38:28 harryf Exp $
//
/**
* @package XML_SaxFilters
* @version $Id: AbstractFilter.php,v 1.5 2003/09/25 17:38:28 harryf Exp $
*/
/**
* Base class for Sax Filters to extend.
* Provides methods for dealing with listeners, parents and writers.
* @access public
* @abstract
* @package XML_SaxFilters
*/
class XML_SaxFilters_AbstractFilter
{
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
     * Sets the child filter to which events are delegated
     * @param object class or subclass of AbstractFilter
     * @return void
     * @access public
     */
    function setChild(& $child)
    {
        $this->child=& $child;
    }

    /**
     * Unsets the child filter
     * @return void
     * @access public
     */
    function unsetChild()
    {
        $this->child->__destroy();
        unset($this->child);
    }

    /**
     * Sets the parent filter allow the child to talk back
     * @param object class or subclass of AbstractFilter
     * @return void
     * @access public
     */
    function setParent(& $parent)
    {
        $this->parent=& $parent;
    }

    /**
     * Breaks the connection to from the child to the parent filter.
     * @return void
     * @access public
     */
    function unsetParent()
    {
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
    function attachToParent (& $child)
    {
        $this->parent->setChild($child);
    }

    /**
     * Calls the parent unsetChild() method removing any child filter
     * from the parent
     * @return void
     * @access public
     */
    function detachFromParent ()
    {
        $this->parent->unsetChild();
    }

    /**
     * Sets the writer
     * @param object class implementing WriterInterface
     * @return void
     * @access public
     */
    function setWriter(& $writer)
    {
        $this->writer=& $writer;
    }

    /**
     * Unsets the writer
     * @return object class implementing WriterInterface
     * @access public
     */
    function & getWriter()
    {
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
     * Start of parsing handler
     * @return void
     * @access protected
     */
    function startDoc()
    {
        if ( isset($this->child) )
        {
            $this->child->startDoc();
        }
    }
    
    /**
     * Sax start namepace handler
     * @param string namespace name
     * @param array namespace URI
     * @return void
     * @access public
     * LMO
     */
    function startNS(& $qname,& $uri) {
        if ( isset($this->child) )
        {
            $this->child->startNS($qname,$uri);
        }
    }
    
    /**
     * Sax end namespace handler
     * @param string namespace name
     * @param array namespace URI
     * @return void
     * @access public
     * LMO
     */
    function endNS(& $qname,& $uri) {
        if ( isset($this->child) )
        {
            $this->child->endNS($qname,$uri);
        }
    }

    /**
     * Sax start element handler
     * @param string element tag name
     * @param array element attributes
     * @return void
     * @access protected
     */
    function open(& $tag,& $attribs)
    {
        if ( isset($this->child) )
        {
            $this->child->open($tag,$attribs);
        }
    }

    /**
     * Sax end element handler
     * @param string element tag name
     * @return void
     * @access protected
     */
    function close(& $tag)
    {
        if ( isset($this->child) )
        {
            $this->child->close($tag);
        }
    }

    /**
     * Sax character data handler
     * @param string contents of element
     * @return void
     * @access protected
     */
    function data(& $data)
    {
        if ( isset($this->child) )
        {
            $this->child->data($data);
        }
    }
    
    /**
     * Sax processing instruction handler
     * @param string target processor
     * @param string instruction for processor
     * @return void
     * @access protected
     */
    function pi(& $target, & $instruction)
    {
        if ( isset($this->child) )
        {
            $this->child->pi($target,$instruction);
        }
    }
    
    /**
     * Escape handler (PEAR::XML_HTMLSax only)
     * @param string contents of escape
     * @return void
     * @access protected
     */
    function escape(& $data)
    {
        if ( isset($this->child) )
        {
            $this->child->escape($data);
        }
    }
    
    /**
     * JSP/ASP markup handler (PEAR::XML_HTMLSax only)
     * @param string contents of escape
     * @return void
     * @access protected
     */
    function jasp(& $data)
    {
        if ( isset($this->child) )
        {
            $this->child->jasp($data);
        }
    }
    
    /**
     * End of parsing handler
     * @return void
     * @access protected
     */
    function endDoc()
    {
        if ( isset($this->child) )
        {
            $this->child->endDoc();
        }
    }
}
?>
