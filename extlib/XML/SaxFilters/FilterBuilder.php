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
// $Id: FilterBuilder.php,v 1.7 2003/09/12 11:20:11 harryf Exp $
//
/**
* @package XML_SaxFilters
* @version $Id: FilterBuilder.php,v 1.7 2003/09/12 11:20:11 harryf Exp $
*/
/**
* Class that, given a map of tag names to other filters, can be used
* to create filters.
* @access public
* @package XML_SaxFilters
*/
class XML_SaxFilters_FilterBuilder
{
    /**
     * Array of FilterMap objects
     * @var array
     * @access private
     */
    var $maps;
    
    /**
     * Constructs FilterBuilder
     * @param array of XML_SaxFilters_FilterMap objects
     * @access public
     */
    function XML_SaxFilters_FilterBuilder(& $maps)
    {
        $this->maps = & $maps;
    }
    /**
     * Makes a filter an adds it as the parent filters child. Checks
     * to see if a filter should be built against the FilterMaps
     * @param object parent filter subclass of AbstractFilter
     * @param string XML opening tag element name
     * @param array XML opening tag attributes (optional)
     * @return boolean true on success
     * @access public
     */
    function attachFilter(& $parent,$tag,$attrs=array())
    {
        foreach ( $this->maps as $map )
        {
            if ( $map->isFilter($tag,$attrs) )
            {
                $child = & $map->makeFilter();
                $child->setParent($parent);
                $parent->setChild($child);
                return TRUE;
            }
        }
        return FALSE;
    }
    /**
     * Removes a filter from the parent, after checking the FilterMaps
     * @param object parent filter subclass of AbstractFilter
     * @param string XML closing tag element name
     * @return boolean true on success
     * @access public
     */
    function detachFilter(& $parent,$name)
    {
        foreach ( $this->maps as $map )
        {
            if ( $map->isFilter($name) )
            {
                $parent->unsetChild();
                return TRUE;
            }
        }
        return FALSE;
    }
}

/**
* Defines a map which is used to identify when an Filter should be created.
* Used by XML_SaxFilters_FilterBuilder to create an remove filters.<br />
* Note: so long as you confirm to the APIs of the isFilter and makeFilter
* methods in this class, expected by FilterBuilder, you can replace it with
* your own (allowing you to so custom stuff with the makeFilter method, for
* example)
* @access public
* @package XML_SaxFilters
* @see XML_SaxFilters_FilterBuilder
*/
class XML_SaxFilters_FilterMap
{
    /**
     * Name of Filter class to create (subclass of AbstractFilter)
     * @var string (default = PEAR_Error) - make sure you replace it
     * @access public
     */
    var $filterName = 'PEAR_Error';
    
    /**
     * Path and name of class file for filter to include
     * when FilterMap::makeFilter is called
     * @var string (default = 'PEAR.php') - make sure you replace it
     * @access public
     */
    var $filterFile = 'PEAR.php';

    /**
     * XML Element name that matches a filter
     * @var string
     * @access public
     */
    var $tag;

    /**
     * Array of XML tag attributes matching a filter
     * @var mixed (default = NULL)
     * @access public
     */
    var $attrs = NULL;
    
    function XML_SaxFilters_FilterMap($class,$tag,$attrs=NULL) {
        $this->filterName = $class;
        $this->tag = $tag;
        $this->attrs = $attrs;
    }

    /**
     * Checks to see a given XML opening tag element maps to a filter.
     * Checks uses a case insensitive comparison of the tag name.
     * If FilterMap was constructed with an array or attributes and the
     * call to isFilter received an array as second argument, is performs
     * a array_diff_assoc() to compare the array (which is case SENSITIVE)
     * @param string XML element tag name
     * @param array (default = NULL) XML element attributes
     * @return boolean TRUE if element is a filter, FALSE if not
     * @access public
     */
    function isFilter($tag,$attrs=NULL)
    {
        $isFilter = FALSE;
        if ( strcasecmp($tag,$this->tag) == 0 ) {
            $isFilter = TRUE;
        }
        if ( !is_null($attrs) && !is_null($this->attrs) ) {
            $array = array_diff_assoc($attrs,$this->attrs);
            if ( count($array) != 0 ) {
                $isFilter = FALSE;
            } else {
                $isFilter = $isFilter & TRUE;
            }
        }
        return $isFilter;
    }

    /**
    * Returns a filter object, based on the filterName and filterFile
    * properties of the FilterMap
    * @return object either subclass of AbstractFilter or instance of PEAR_Error
    * @access public
    */
    function & makeFilter()
    {
        if ( !class_exists($this->filterName) ) {
            require_once $this->filterFile;
        }
        return new $this->filterName;
    }
}
?>