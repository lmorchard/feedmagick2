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
// $Id: AbstractParser.php,v 1.4 2003/09/12 11:20:11 harryf Exp $
//
/**
* @package XML_SaxFilters
* @version $Id: AbstractParser.php,v 1.4 2003/09/12 11:20:11 harryf Exp $
*/
/**
* Base class for Sax Parsers to extend.
* @access public
* @abstract
* @package XML_SaxFilters
*/
class XML_SaxFilters_AbstractParser
{
    /**
     * Stores the reader
     *
     * @var object
     * @access private
     */
    var $reader;

    /**
     * Stores the child filter
     *
     * @var object
     * @access private
     */
    var $child;
    
    /**
     * Error stored here
     * @var PEAR_Error
     * @access private
     */
    var $error = NULL;

    /**
     * Constructs AbstractParser
     * @see XML_SaxFilters_IO_ReaderInterface
     * @param object class implementing ReaderInterface
     * @access public
     * @abstract
     */
    function XML_SaxFilters_AbstractParser(& $reader)
    {
        $this->reader = & $reader;
    }

    /**
     * Sets the child
     * @param object class or subclass of AbstractFilter
     * @return void
     */
    function setChild(& $child)
    {
        $this->child=& $child;
    }

    /**
     * Unsets the child
     * @return void
     */
    function unsetChild()
    {
        unset($this->child);
    }
    
    /**
    * Returns the error if there are any.
    * @return mixed PEAR_Error or NULL if no errors
    * @access public
    */
    function getError()
    {
        return $this->error;
    }
    
    /**
     * Handler called when parsing begins
     * @return void
     * @access public
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
     * @param object instance of the parser
     * @param string namespace name
     * @param array namespace URI
     * @return void
     * @access public
     * LMO
     */
    function startNS($parser,& $qname,& $uri) {
        if ( isset($this->child) )
        {
            $this->child->startNS($qname,$uri);
        }
    }
    
    /**
     * Sax end namespace handler
     * @param object instance of the parser
     * @param string namespace name
     * @param array namespace URI
     * @return void
     * @access public
     * LMO
     */
    function endNS($parser,& $qname,& $uri) {
        if ( isset($this->child) )
        {
            $this->child->endNS($qname, $uri);
        }
    }

    /**
     * Sax start element handler
     * @param object instance of the parser
     * @param string element tag name
     * @param array element attributes
     * @return void
     * @access public
     */
    function open($parser,& $tag,& $attribs)
    {
        if ( isset($this->child) )
        {
            $this->child->open($tag,$attribs);
        }
    }

    /**
     * Sax end element handler
     * @param object instance of the parser
     * @param string element tag name
     * @return void
     * @access public
     */
    function close($parser,& $tag)
    {
        if ( isset($this->child) )
        {
            $this->child->close($tag);
        }
    }

    /**
     * Sax character data handler
     * @param object instance of the parser
     * @param string contents of element
     * @return void
     * @access public
     */
    function data($parser,& $data)
    {
        if ( isset($this->child) )
        {
            $this->child->data($data);
        }
    }
    
    /**
     * Sax processing instruction handler
     * @param object instance of the parser
     * @param string target processor
     * @param string instruction to processor
     * @return void
     * @access public
     */
    function pi($parser,& $target,& $instruction)
    {
        if ( isset($this->child) )
        {
            $this->child->pi($target,$instruction);
        }
    }
    
    /**
     * Handler called when parsing finishes
     * @return void
     * @access public
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
