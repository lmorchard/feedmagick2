<?xml version='1.0' encoding='utf-8'?>
<!--
     $Id: atom100.xsl 544 2004-12-04 05:15:20Z deusx $
     
     $Log: atom100.xsl,v $
     Revision 1.3  2004/09/08 05:41:48  deusx
     Added permalink as ID

     Revision 1.2  2004/09/08 05:40:54  deusx
     Added escaping

     Revision 1.1  2004/09/07 23:35:38  deusx
     Added preliminary Atom support

-->

<xsl:stylesheet version='1.0' 
                xmlns:xsl='http://www.w3.org/1999/XSL/Transform'
                xmlns:html="http://www.w3.org/1999/xhtml"
                xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
                xmlns:dyn="http://exslt.org/dynamic"
                xmlns:date="http://exslt.org/dates-and-times"
                extension-element-prefixes="dyn date">
  
  <!-- Atom v0.3 -->

  <xsl:template name="atom100.title"><xsl:call-template name="title" /></xsl:template>
  <xsl:template name="atom100.link"><xsl:call-template name="link" /></xsl:template>
  <xsl:template name="atom100.description"><xsl:call-template name="description" /></xsl:template>
  <xsl:template name="atom100.item.link"><xsl:call-template name="item.link" /></xsl:template>
  <xsl:template name="atom100.item.title"><xsl:call-template name="item.title" /></xsl:template>
  <xsl:template name="atom100.item.description"><xsl:call-template name="item.description" /></xsl:template>
  
  <xsl:template name="atom100">
    <xsl:variable name="link"></xsl:variable>

    <feed xmlns="http://purl.org/atom/ns#" xml:lang="{$language}" version="0.3">

      <title><xsl:call-template name="atom100.title" /></title>
      <link rel="alternate" type="text/html">
        <xsl:attribute name="href">
          <xsl:call-template name="atom100.link" />
        </xsl:attribute>
      </link>
      <tagline><xsl:call-template name="atom100.description" /></tagline>
      <generator url="http://www.decafbad.com/twiki/bin/view/Main/XslScraper">
        XslScraper by l.m.orchard from 0xDECAFBAD
      </generator>
      <author>
        <name>unknown</name>
      </author>
      <modified><xsl:value-of select="date:date-time()" /></modified>

      <xsl:choose> 
        <xsl:when test="$items.order='reverse'">
          <xsl:for-each select="dyn:evaluate($items.path)">
            <xsl:sort select="position()" order="descending" data-type="number" />
            <xsl:if test="position() &lt;= $maxItems">            
              <xsl:call-template name="atom100.item" />
            </xsl:if>
          </xsl:for-each>
        </xsl:when>
        <xsl:otherwise>
          <xsl:for-each select="dyn:evaluate($items.path)">
            <xsl:if test="position() &lt;= $maxItems">
              <xsl:call-template name="atom100.item" />
            </xsl:if>
          </xsl:for-each>
        </xsl:otherwise>
      </xsl:choose>
      
    </feed>
  </xsl:template>

  <xsl:template name="atom100.item">
    <entry xmlns="http://purl.org/atom/ns#">
      <title><xsl:call-template name="atom100.item.title" /></title>
      <link rel="alternate" type="text/html">
        <xsl:attribute name="href">
          <xsl:call-template name="atom100.item.link" />
        </xsl:attribute>
      </link>
      <id><xsl:call-template name="atom100.item.link" /></id>
      <!-- TODO: Need an entry modified date, which is a problem without persistent data -->
      <modified>2004-09-08T11:42:33-04:00</modified>
      <!-- TODO: Need an entry issued date, which is a problem without persistent data -->
      <issued>2004-09-08T11:42:33-04:00</issued>
      <summary type="text/html" mode="escaped">
        <xsl:call-template name="atom100.item.description" />
      </summary>
    </entry>
  </xsl:template>
  
 </xsl:stylesheet>
