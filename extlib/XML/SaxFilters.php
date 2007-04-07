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
// $Id: XML_SaxFilters.php,v 1.5 2003/09/12 11:21:37 harryf Exp $
//
/**
* @package XML_SaxFilters
* @version $Id: XML_SaxFilters.php,v 1.5 2003/09/12 11:21:37 harryf Exp $
*/
/**
* Includes
*/
if (!defined('XML_SAXFILTERS')) {
    define('XML_SAXFILTERS', 'XML/');
}
/**
* Include abstract filter
*/
require_once(XML_SAXFILTERS.'SaxFilters/AbstractFilter.php');
/**
* Creates the SaxFilter parser and associated IO readerType
* <pre>
* require_once('XML/SaxFilters.php');
*
* // Create a parser using Expat and a string IO reader, where
* // $xmlstring contains the XML document
* $parser = & XML_SaxFilters_createParser('Expat','String',$xmlstring);
* 
* // Add some user defined filter to the parser
* $parser->setChild(new MyFilter());
* $parser->parse();
* </pre>
* @param string parser type ('Expat' or 'HTMLSax')
* @param string reader type ('File' or 'String')
* @param mixed input source (string containing XML, filename or array)
* @access public
* @return mixed
* @package XML_SaxFilters
*/
function &XML_SaxFilters_createParser($parserType,$readerType,$input)
{
    switch ( strtolower($readerType) ) {
        case 'file':
            require_once(XML_SAXFILTERS.'/SaxFilters/IO/FileReader.php');
            $reader = & new XML_SaxFilters_IO_FileReader($input);
        break;
        case 'string':
            require_once(XML_SAXFILTERS.'/SaxFilters/IO/StringReader.php');
            $reader = & new XML_SaxFilters_IO_StringReader($input);
        break;
        default:
            require_once('PEAR.php');
            return PEAR::raiseError('Unrecognized reader type: '.$readerType);
        break;
    }
    switch ( strtolower($parserType) ) {
        case 'expat':
            require_once(XML_SAXFILTERS.'/SaxFilters/ExpatParser.php');
            $parser = & new XML_SaxFilters_ExpatParser($reader);
        break;
        case 'htmlsax':
            require_once(XML_SAXFILTERS.'/SaxFilters/HTMLSaxParser.php');
            $parser = & new XML_SaxFilters_HTMLSaxParser($reader);
        break;
        default:
            require_once('PEAR.php');
            return PEAR::raiseError('Unrecognized parser type: '.$parserType);
        break;
    }
    return $parser;
}

/**
* Utility to help chain an array of filters together to a parser.
* <pre>
* $parser = & XML_SaxFilters_createParser('Expat','String',$xmlstring);
* 
* $filters = array();
* $filters[] = & new FilterA(); // Chained to the parser
* $filters[] = & new FilterB(); // Chained to FilterA
* $filters[] = & new FilterC(); // Chained to FilterB
*
* // Build the chain
* XML_SaxFilters_buildChain($parser,$filters);
*
* $parser->parse();
* </pre>
* @param object extending XMLSaxFilters_AbstractParser
* @param mixed array of filter objects or single filter object
* @access public
* @return boolean TRUE on success
* @package XML_SaxFilters
*/
function XML_SaxFilters_buildChain(& $Parser, & $filters) {
    if ( is_array($filters) && count($filters) > 0 ) {
        $start = TRUE;
        foreach ( array_keys($filters) as $key ) {
            if ( $start ) {
                $Parser->setChild($filters[$key]);
                $start = FALSE;
            } else {
                $filters[$key-1]->setChild($filters[$key]);
            }
        }
        return TRUE;
    } else if ( is_subclass_of($filters,'xml_saxfilters_abstractfilter') ) {
        $Parser->setChild($filters);
        return TRUE;
    }
    return FALSE;
}
?>