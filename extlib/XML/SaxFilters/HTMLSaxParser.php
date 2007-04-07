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
// $Id: HTMLSaxParser.php,v 1.6 2003/09/12 12:02:41 harryf Exp $
//
/**
* Implements PEAR::XML_HTMLSax for Sax filtering
* @package XML_SaxFilters
* @version $Id: HTMLSaxParser.php,v 1.6 2003/09/12 12:02:41 harryf Exp $
*/
/**
* Required classes
*/
if (!defined('XML_SAXFILTERS')) {
    define('XML_SAXFILTERS', 'XML/');
}
if (!defined('XML_HTMLSAX')) {
    define('XML_HTMLSAX', 'XML/');
}
require_once(XML_HTMLSAX.'XML_HTMLSax.php');
require_once(XML_SAXFILTERS.'SaxFilters/AbstractParser.php');
/**
 * HtmlSaxParser adapts XML_HTMLSax parser to allow filtering
 * on badly formed XML<br />
 * Note: some leaky abstraction turns up here - notice the start handler
 * and the escape and jasp handlers - these are specific to PEAR::XML_HTMLSax
 * @access public
 * @package XML_SaxFilters
 */
class XML_SaxFilters_HtmlSaxParser extends XML_SaxFilters_AbstractParser/* implements XML_SaxFilters_ParserInterface */
{
    /**
     * Stores an instance of the parser
     *
     * @var object
     * @access private
     */
    var $parser;

    /**
     * Constructs HtmlSaxParser
     * @param object class implementing ReaderInterface
     * @access public
     */
    function XML_SaxFilters_HtmlSaxParser(& $reader)
    {
        parent::XML_SaxFilters_AbstractParser($reader);
        $this->parser=& new XML_HTMLSax();
        $this->parser->set_object($this);
        $this->parser->set_element_handler('open','close');
        $this->parser->set_data_handler('data');
        $this->parser->set_pi_handler('pi');
        $this->parser->set_escape_handler('escape');
        $this->parser->set_jasp_handler('jasp');
        $this->parser->set_option('XML_OPTION_FULL_ESCAPES');
        $this->parser->set_option('XML_OPTION_LINEFEED_BREAK');
        $this->parser->set_option('XML_OPTION_ENTITIES_PARSED');
    }
    
    /**
     * Sax start element handler - note leakiness of fourth argument
     * @param object instance of the parser
     * @param string element name
     * @param array element attributes
     * @param boolean whether it's an "empty" tag e.g. br/
     * @return void
     * @access public
     */
    function open($parser,& $tag,& $attribs, $empty)
    {
        $this->child->open($tag,$attribs,$empty);
    }
    
    /**
     * Sax end element handler - note leakiness of second argument
     * @param object instance of the parser
     * @param string element name
     * @param boolean whether it's an "empty" tag e.g. br/
     * @return void
     * @access public
     */
    function close($parser,& $tag,$empty)
    {
        $this->child->close($tag,$empty);
    }    
    
    /**
     * For XML escape strings
     * @param object instance of the parser
     * @param string contents of XML escape string
     * @return void
     * @access public
     */
    function escape($parser,& $data)
    {
        $this->child->escape($data);
    }
    
    /**
     * For JSP/ASP markup
     * @param object instance of the parser
     * @param string contents of JSP/ASP code block
     * @return void
     * @access public
     */
    function jasp($parser,& $data)
    {
        $this->child->jasp($data);
    }

    /**
     * Sets a Sax parser option
     * @param string option name
     * @param string option value
     * @return boolean
     * @access public
     */
    function parserSetOption($opt,$val) {
        return $this->parser->set_option($opt, $val);
    }

    /**
     * Parse the XML stream
     * @return void
     * @access public
     */
    function parse() {
        $this->startDoc();
        while ( $data = $this->reader->read() ) {
            if ( strtolower(get_class($data)) == 'pear_error' ) {
                $this->error = $data;
                return FALSE;
            }        
            $this->parser->parse($data);
        }
        $this->endDoc();
        return TRUE;
    }
}
?>