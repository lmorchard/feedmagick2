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
// $Id: ExpatParser.php,v 1.6 2003/09/12 11:20:11 harryf Exp $
//
/**
* @package XML_SaxFilters
* @version $Id: ExpatParser.php,v 1.6 2003/09/12 11:20:11 harryf Exp $
*/
/**
* Required classes
*/
if (!defined('XML_SAXFILTERS')) {
    define('XML_SAXFILTERS', 'XML/');
}
require_once(XML_SAXFILTERS.'SaxFilters/AbstractParser.php');
/**
* ExpatParser for the native PHP SAX XML extension
* @access public
* @package XML_SaxFilters
*/
class XML_SaxFilters_ExpatParser extends XML_SaxFilters_AbstractParser /* implements XML_SaxFilters_ParserInterface */
{
    /**
     * Stores an instance of the parser
     * @var resource
     * @access private
     */
    var $parser;

    /**
     * Constructs ExpatParser
     * Note: modify the buffer for large documents
     * @see XML_SaxFilters_IO_ReaderInterface
     * @param object class implementing ReaderInterface
     * @access public
     */
    function XML_SaxFilters_ExpatParser(& $reader)
    {
        parent::XML_SaxFilters_AbstractParser($reader);

        // LMO
        // TODO: Find a proper fix for using a namespace-aware parser.
        // $this->parser=xml_parser_create_ns();
        $this->parser=xml_parser_create();
        
        xml_set_object($this->parser,$this);
        xml_set_element_handler($this->parser,'open','close');
        xml_set_character_data_handler($this->parser,'data');
        xml_set_processing_instruction_handler($this->parser,'pi');
        
        # LMO
        xml_set_start_namespace_decl_handler($this->parser, 'startNS');
        xml_set_end_namespace_decl_handler($this->parser, 'endNS');

        xml_parser_set_option($this->parser,XML_OPTION_CASE_FOLDING,0);
    }

    /**
     * Sets a Sax parser option
     * @param string option name
     * @param string option value
     * @access public
     */
    function parserSetOption($opt,$val)
    {
        return xml_parser_set_option ( $this->parser, $opt, $val);
    }

    /**
     * Parse the XML stream
     * @return boolean TRUE on successful parsing. Use getError if FALSE
     * @access public
     */
    function parse()
    {
        $this->startDoc();
        while ($data = $this->reader->read()){
            if ( strtolower(get_class($data)) == 'pear_error' ) {
                $this->error = $data;
                return FALSE;
            }
            if (!xml_parse($this->parser, $data, $this->reader->isFinal())) {
                $errorString=xml_error_string(xml_get_error_code($this->parser));
                $line=xml_get_current_line_number($this->parser);
                require_once 'PEAR.php';
                $this->error = new PEAR_Error('Parser error: '.$errorString.' on line '.
                    $line.' in XML document');
                return FALSE;
            }
        }
        $this->endDoc();
        xml_parser_free($this->parser);
        return TRUE;
    }
}
?>
