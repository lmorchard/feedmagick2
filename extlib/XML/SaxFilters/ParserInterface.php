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
// $Id: ParserInterface.php,v 1.4 2003/09/12 11:20:11 harryf Exp $
//
/**
* @package XML_SaxFilters
* @version $Id: ParserInterface.php,v 1.4 2003/09/12 11:20:11 harryf Exp $
*/
/**
* ParserInterface defines methods which SaxParsers must implement
* Note: this class is never actually used anywhere - just for documentation
* @package XML_SaxFilters
*/
class XML_SaxFilters_ParserInterface
{
    /**
     * Sets a Sax parser option
     * @param string option name
     * @param string option value
     * @access public
     * @return mixed
     */
    function parserSetOption($opt,$val) {}

    /**
     * Tells the SAX parser to parse
     * @return mixed PEAR Error or true
     */
    function parse() {}
}
?>