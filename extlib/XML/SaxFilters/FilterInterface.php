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
// $Id: FilterInterface.php,v 1.4 2003/09/12 11:20:11 harryf Exp $
//
/**
* @package XML_SaxFilters
* @version $Id: FilterInterface.php,v 1.4 2003/09/12 11:20:11 harryf Exp $
*/
/**
* FilterInterface which shows the available methods and their parameters
* that your SaxFilters can (optionally) implement<br />
* Note: this class is never actually used anywhere - just for documentation
* @package XML_SaxFilters
*/
class XML_SaxFilters_FilterInterface
{
    /**
     * Start of parsing handler
     * @return void
     * @access protected
     */
    function startDoc() {}
    
    /**
     * Sax start element handler
     * @param string element tag name
     * @param array element attributes
     * @return void
     * @access protected
     */
    function open(& $tag,& $attribs) {}

    /**
     * Sax end element handler
     * @param string element tag name
     * @return void
     * @access protected
     */
    function close(& $tag) {}

    /**
     * Sax character data handler
     * @param string contents of element
     * @return void
     * @access protected
     */
    function data(& $data) {}
    
    /**
     * Sax processing instruction handler
     * @param string target processor
     * @param string instruction to processor
     * @return void
     * @access protected
     */
    function pi(& $target,& $instruction){}
    
    /**
     * Escape handler (PEAR::XML_HTMLSax only)
     * @param string contents of escape
     * @return void
     * @access protected
     */
    function escape(& $data) {}
    
    /**
     * JSP/ASP markup handler (PEAR::XML_HTMLSax only)
     * @param string contents of escape
     * @return void
     * @access protected
     */
    function jasp(& $data) {}
    
    /**
     * End of parsing handler
     * @return void
     * @access protected
     */
    function endDoc() {}
}
?>