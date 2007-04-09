<?xml version='1.0' encoding='iso-8859-1'?>
<!--
     $Id: jlist.xsl 544 2004-12-04 05:15:20Z deusx $
     
     $Log: jlist.xsl,v $
     Revision 1.3  2004/05/23 22:27:46  deusx
     New scrapers, tweaked scrapers

     Revision 1.2  2003/09/01 22:00:09  deusx
     Working on adding comment headers and license info to files

-->

<!-- scraper variables
     source url: http://www.jlist.com/UPDATES/7/
     output name: jlist.rss
     -->

<xsl:stylesheet version='1.0' 
  xmlns:xsl='http://www.w3.org/1999/XSL/Transform'
  xmlns:html="http://www.w3.org/1999/xhtml"
  xmlns:dyn="http://exslt.org/dynamic"
  xmlns:str="http://exslt.org/strings">
  
  <xsl:import href="xsl/scrapers/lib/scraper.xsl" />
  <xsl:import href="xsl/scrapers/lib/str.replace.template.xsl" />

  <xsl:variable name="base">http://www.jlist.com/</xsl:variable>
  <xsl:variable name="title.path">/html:html/html:head/html:title</xsl:variable>  
  <xsl:variable name="items.path">//html:table[@class='pt']</xsl:variable>
  <xsl:variable name="item.title.path">./html:tr[position()=2]/html:td</xsl:variable>
  <xsl:template name="item.link">http://www.jlist.com/PRODUCT/<xsl:value-of select="./html:tr[2]/html:td/html:b" /></xsl:template>
  <xsl:variable name="item.link.path">./html:tr[position()=3]/html:td[position()=2]/html:a/@href</xsl:variable>
  <xsl:variable name="item.description.path">.</xsl:variable>

</xsl:stylesheet>
