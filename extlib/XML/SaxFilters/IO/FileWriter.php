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
// $Id: FileWriter.php,v 1.6 2003/09/12 11:15:15 harryf Exp $
//
/**
* @package XML_SaxFilters
* @version $Id: FileWriter.php,v 1.6 2003/09/12 11:15:15 harryf Exp $
*/
/**
* Required classes
*/
if (!defined('XML_SAXFILTERS')) {
    define('XML_SAXFILTERS', 'XML/');
}
require_once(XML_SAXFILTERS.'SaxFilters/IO/FileReader.php');
/**
 * FileWriter writes data to a file
 * @access public
 * @package XML_SaxFilters
 */
class XML_SaxFilters_IO_FileWriter /* implements XML_SaxFilters_IO_WriterInterface */
{
    /**
     * Name of file to write to: /path/filename
     * @var string
     * @access private
     */
    var $fileName;

    /**
     * Whether to append to an existing file or replace contents
     * @var boolean
     * @access private
     */
    var $append;

    /**
     * PHP File resource
     * @var resource
     * @access private
     */
    var $fp = false;
    
    /**
    * Error information
    * @var PEAR_Error (default = NULL)
    * @access private
    */
    var $error = NULL;

    /**
     * FileWriter Constructor
     * @param string path and name of file
     * @param boolean whether to append to or replace the file
     * @access public
     */
    function XML_SaxFilters_IO_FileWriter($fileName, $append = FALSE)
    {
        $this->fileName = $fileName;
        $this->append = $append;
    }

    /**
     * Writes some data to file
     * @param string data to write
     * @access public
     * @return mixed TRUE on success, FALSE on fail - see getError
     */
    function write($data)
    {
        if ( !$this->fp ) {
            $this->append == FALSE ? $mode = 'w' : $mode = 'a';
            if ( !$this->fp = fopen($this->fileName, $mode) ) {
                require_once 'PEAR.php';
                $this->error = PEAR::raiseError('Unable to open file: '.
                    $this->fileName);
                return FALSE;
            }
        }
        if ( !fwrite($this->fp,$data) ) {
            require_once 'PEAR.php';
            $this->error = $this->raiseError('Unable to write to file: '.
                $this->fileName);
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * Returns a FileReader
     * @param int buffer (default = 4096)
     * @access public
     * @return object instance of FileReader
     */
    function & getReader($buffer=4096)
    {
        return new XML_SaxFilters_IO_FileReader($this->fileName,$buffer);
    }

    /**
     * Close the file
     * @access public
     * @return boolean
     */
    function close()
    {
        return fclose($this->fp);
    }
}
?>