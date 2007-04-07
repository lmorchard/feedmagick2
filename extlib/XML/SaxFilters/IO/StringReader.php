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
// $Id: StringReader.php,v 1.4 2003/09/12 11:15:15 harryf Exp $
//
/**
* @package XML_SaxFilters
* @version $Id: StringReader.php,v 1.4 2003/09/12 11:15:15 harryf Exp $
*/
/**
* StringReader streams data from a string for use by an XML parser
* @access public
* @package XML_SaxFilters
*/
class XML_SaxFilters_IO_StringReader /* implements XML_SaxFilters_IO_ReaderInterface */
{
    /**
     * String to read from
     * @var string
     * @access private
     */
    var $string;

    /**
     * Size in character to read
     * @var int
     * @access private
     */
    var $buffer;

    /**
     * Tracks the position in the string
     * @var int
     * @access private
     */
    var $position;

    /**
     * StringReader Constructor
     * @param string string to read from
     * @param int buffer size to read in characers
     * @access public
     */
    function XML_SaxFilters_IO_StringReader(& $string,$buffer=4096)
    {
        $this->string = & $string;
        $this->buffer = $buffer;
        $this->position = 0;
    }

    /**
     * Read some data from the string
     * @access public
     * @return mixed data of FALSE when finished
     */
    function read()
    {
        if ( strlen($this->string) > $this->position ) {
            $slice = substr($this->string,$this->position,$this->buffer);
            $this->position += $this->buffer;
            return $slice;
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
        if ( strlen($this->string) > $this->position )
            return FALSE;
        else
            return TRUE;
    }
}
?>