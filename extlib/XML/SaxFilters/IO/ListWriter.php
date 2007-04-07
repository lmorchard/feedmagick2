<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Luis Argerich <lrargerich@yahoo.com> Original Author        |
// | Authors: Harry Fuecks <hfuecks@phppatterns.com> Port to PEAR + more  |
// +----------------------------------------------------------------------+
//
// $Id: StructWriter.php,v 1.6 2003/09/12 11:15:15 harryf Exp $
//
/**
* Required classes
*/
if (!defined('XML_SAXFILTERS')) {
    define('XML_SAXFILTERS', 'XML/');
}
require_once(XML_SAXFILTERS.'SaxFilters/IO/ListReader.php');
/**
 * ListWriter writes variables to a list
 * @access public
 * @package XML_SaxFilters
 */
class XML_SaxFilters_IO_ListWriter /* implements XML_SaxFilters_IO_WriterInterface */
{
    /**
     * List to write to
     * @var array
     * @access private
     */
    var $list;

    /**
     * ListWriter Constructor
     * @access public
     */
    function XML_SaxFilters_IO_ListWriter()
    {
        $this->list = array();
    }

    /**
     * Adds elemment to the list (it won't allow you to write values which
     * evaluate to false)
     * @param mixed data to write
     * @access public
     * @return boolean TRUE on success, FALSE is you try to write a FALSE value
     */
    function write(& $data)
    {
        if ( $data ) {
            $this->list[]=& $data;
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Returns a ListReader
     * @access public
     * @return XML_SaxFilters_IO_ListReader
     */
    function & getReader()
    {
        return new XML_SaxFilters_IO_ListReader($this->list);
    }

    /**
     * "Close" the list - does nothing
     * @access public
     * @return boolean
     */
    function close()
    {
        return TRUE;
    }
}
?>