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
// $Id: StructReader.php,v 1.4 2003/09/12 11:15:15 harryf Exp $
//
/**
* @package XML_SaxFilters
* @version $Id: StructReader.php,v 1.4 2003/09/12 11:15:15 harryf Exp $
*/
/**
 * ListReader iterates over a list. This isn't
 * meant for use directly by the filters but simply
 * to be returned by ListWriter
 * @access public
 * @package XML_SaxFilters
 */
class XML_SaxFilters_IO_ListReader /* implements XML_SaxFilters_IO_ReaderInterface */
{
    /**
     * List to read from
     * @var array
     * @access private
     */
    var $list;

    /**
     * Keeps track of the array pointer
     * @var int
     * @access private
     */
    var $pointer;

    /**
     * ListReader Constructor
     * @param array list to read from
     * @access public
     */
    function XML_SaxFilters_IO_ListReader(& $list)
    {
        $this->list = & $list;
        $this->pointer = -1;
    }

    /**
     * Returns one element from the list
     * @access public
     * @return mixed element from the list (a reference)
     */
    function & read()
    {
        $element = each ( $this->list );
        if ( $element ) {
            $this->pointer ++;
            return $this->list[$element['key']];
        } else {
            return FALSE;
        }
    }

    /**
     * Indicates whether the reader has reached the end of the data source
     * @access public
     * @return boolean
     */
    function isFinal()
    {
        // Must do this every time as list may be being written to at same time
        $size = count($this->list) - 1;
        if ( $this->pointer >= $size )
            return TRUE;
        else
            return FALSE;
    }
}
?>