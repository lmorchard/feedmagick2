<?xml version='1.0' encoding='utf-8'?>
<!--
     $Id: scraper.xsl 544 2004-12-04 05:15:20Z deusx $
     
     $Log: scraper.xsl,v $
     Revision 1.5  2004/09/08 05:47:12  deusx
     Tweaked default language code

     Revision 1.4  2004/09/07 23:35:38  deusx
     Added preliminary Atom support

     Revision 1.3  2003/12/05 03:01:35  deusx
     Altered title to add project url

     Revision 1.2  2003/09/01 22:08:47  deusx
     Working on adding CVS comment headers, attribution,  and license info to files

-->

<xsl:stylesheet version='1.0' 
  xmlns:xsl='http://www.w3.org/1999/XSL/Transform'
  xmlns:html="http://www.w3.org/1999/xhtml"
  xmlns:dyn="http://exslt.org/dynamic"
  xmlns:str="http://exslt.org/strings"
  extension-element-prefixes="dyn str">
  
  <xsl:import href="rss091.xsl" />
  <xsl:import href="rss100.xsl" />
  <xsl:import href="rss200.xsl" />
  <xsl:import href="atom030.xsl" />
  <xsl:import href="atom100.xsl" />
  
  <xsl:import href="escape-html.xsl" />
  
  <xsl:output method="xml" version="1.0" encoding="utf-8" indent="yes" />

  <!-- Default variables -->
  <xsl:variable name="format"      select="'rss091'" />
  <xsl:variable name="language"    select="'en-us'" />
  <xsl:variable name="maxItems"    select="'15'" />
  <xsl:variable name="title.path"  select="'/html:html/html:head/html:title'" />
  <xsl:variable name="items.path"  select="'//html:a'" />
  <xsl:variable name="items.order" select="'normal'" />
  
  <xsl:variable name="item.title.path" />
  <xsl:variable name="item.link.path" />
  <xsl:variable name="item.description.path" />

  <!-- -->
  <xsl:template name="title">
    <xsl:value-of select="normalize-space(dyn:evaluate($title.path))"/> (scraped)
  </xsl:template>
  <xsl:template name="description">
    Generated from <xsl:value-of select="normalize-space(dyn:evaluate($title.path))"/>
  (<xsl:call-template name="link" />) using XslScraper
  (http://www.decafbad.com/twiki/bin/view/Main/XslScraper)
  </xsl:template>
  <xsl:template name="link">
    <xsl:value-of select="normalize-space($base)"/>
  </xsl:template>
  <xsl:template name="item.link">
    <xsl:value-of select="normalize-space(dyn:evaluate($item.link.path))"/>
  </xsl:template>
  <xsl:template name="item.title">
    <xsl:value-of select="normalize-space(dyn:evaluate($item.title.path))"/>
  </xsl:template>
  <xsl:template name="item.description">
    <xsl:apply-templates select="dyn:evaluate($item.description.path)" mode="escape-html"/>
  </xsl:template>
  
  <!-- Allow a parameterized choice between formats -->
  <xsl:template match="/">
    <xsl:choose> 
      <xsl:when test="$format='rss091'"><xsl:call-template name="rss091" /></xsl:when>
      <xsl:when test="$format='rss100'"><xsl:call-template name="rss100" /></xsl:when>
      <xsl:when test="$format='rss200'"><xsl:call-template name="rss200" /></xsl:when>
      <xsl:when test="$format='atom030'"><xsl:call-template name="atom030" /></xsl:when>
      <xsl:when test="$format='atom100'"><xsl:call-template name="atom100" /></xsl:when>
      <xsl:otherwise><xsl:call-template name="rss100" /></xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  
</xsl:stylesheet>
