<?xml version='1.0' encoding='iso-8859-1'?>
<!--
     $Id: insertcredit.xsl 544 2004-12-04 05:15:20Z deusx $
     
     $Log: insertcredit.xsl,v $
     Revision 1.2  2004/04/08 00:11:59  deusx
     Took out large max items

     Revision 1.1  2004/03/29 22:05:07  deusx
     New scrapers & tweaks

-->

<!-- scraper variables
     source url: http://www.insertcredit.com/
     output name: insertcredit.rss
     -->

<xsl:stylesheet version='1.0' 
  xmlns:xsl='http://www.w3.org/1999/XSL/Transform'
  xmlns:html="http://www.w3.org/1999/xhtml"
  xmlns:dyn="http://exslt.org/dynamic"
  xmlns:str="http://exslt.org/strings">
  
  <xsl:import href="www/xsl/scrapers/lib/scraper.xsl" />

  <xsl:variable name="base">http://www.insertcredit.com/</xsl:variable>
  <xsl:variable name="items.path">//html:p[@id='title']</xsl:variable>
  <xsl:variable name="item.title.path">.</xsl:variable>
  <xsl:variable name="item.link.path">$base</xsl:variable>
  <xsl:variable name="item.description.path">../../following-sibling::html:tr[2]</xsl:variable>  
</xsl:stylesheet>
