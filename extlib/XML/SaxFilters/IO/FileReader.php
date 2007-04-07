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
// $Id: FileReader.php,v 1.4 2003/09/12 11:15:15 harryf Exp $
//
/**
* @package XML_SaxFilters
* @version $Id: FileReader.php,v 1.4 2003/09/12 11:15:15 harryf Exp $
*/
/**
* FileReader streams data from a file for use by an XML parser
* @access public
* @package XML_SaxFilters
*/
class XML_SaxFilters_IO_FileReader /* implements XML_SaxFilters_IO_ReaderInterface */
{
    /**
     * Name of source file: /path/filename
     * @var string
     * @access private
     */
    var $fileName;

    /**
     * Buffer size for file read
     * @var int
     * @access private
     */
    var $buffer;

    /**
     * PHP File resource
     * @var resource (default = NULL)
     * @access private
     */
    var $fp = NULL;

    /**
     * FileReader Constructor
     * @param string path and name of file
     * @param int buffer size to read
     * @access public
     */
    function XML_SaxFilters_IO_FileReader($fileName,$buffer=4096)
    {
        $this->fileName = $fileName;
        $this->buffer = $buffer;
    }

    /**
     * Returns some data from the source
     * @access public
     * @return mixed string data, FALSE which finished, PEAR_ERROR on error
     */
    function read()
    {
        if ( !$this->fp ) {
            if ( !$this->fp = fopen($this->fileName, 'r') ) {
                require_once 'PEAR.php';
                return PEAR::raiseError('Unable to open file: '.
                    $this->fileName);
            }
        }
        if ( $data = fread($this->fp,$this->buffer) ) {
            return $data;
        } else {
            fclose($this->fp);
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
        return feof($this->fp);
    }
}
?>