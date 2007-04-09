<?xml version='1.0' encoding='iso-8859-1'?>
<!--
     $Id: rss091.xsl 544 2004-12-04 05:15:20Z deusx $
     
     $Log: rss091.xsl,v $
     Revision 1.2  2003/09/01 22:08:47  deusx
     Working on adding CVS comment headers, attribution,  and license info to files

-->

<xsl:stylesheet version='1.0' 
  xmlns:xsl='http://www.w3.org/1999/XSL/Transform'
  xmlns:html="http://www.w3.org/1999/xhtml"
  xmlns:dyn="http://exslt.org/dynamic">

  <!-- RSS v0.91 -->

  <xsl:template name="rss091.title"><xsl:call-template name="title" /></xsl:template>
  <xsl:template name="rss091.link"><xsl:call-template name="link" /></xsl:template>
  <xsl:template name="rss091.description"><xsl:call-template name="description" /></xsl:template>
  <xsl:template name="rss091.item.link"><xsl:call-template name="item.link" /></xsl:template>
  <xsl:template name="rss091.item.title"><xsl:call-template name="item.title" /></xsl:template>
  <xsl:template name="rss091.item.description"><xsl:call-template name="item.description" /></xsl:template>
  
  <xsl:template name="rss091">
    <rss version="0.91" xmlns:html="http://www.w3.org/1999/xhtml">
      <channel>
        <title><xsl:call-template name="rss091.title" /></title>
        <link><xsl:call-template name="rss091.link" /></link>
        <description><xsl:call-template name="rss091.description" /></description>
        <language><xsl:value-of select="$language" /></language>
        
        <xsl:choose> 
          <xsl:when test="$items.order='reverse'">
            <xsl:for-each select="dyn:evaluate($items.path)">
              <xsl:sort select="position()" order="descending" data-type="number" />
              <xsl:if test="position() &lt;= $maxItems">            
                <xsl:call-template name="rss091.item" />
              </xsl:if>
            </xsl:for-each>
          </xsl:when>
          <xsl:otherwise>
            <xsl:for-each select="dyn:evaluate($items.path)">
              <xsl:if test="position() &lt;= $maxItems">
                <xsl:call-template name="rss091.item" />
              </xsl:if>
            </xsl:for-each>
          </xsl:otherwise>
        </xsl:choose>
        
      </channel>
    </rss>
  </xsl:template>

  <xsl:template name="rss091.item">
    <item>
      <title><xsl:call-template name="rss091.item.title" /></title>
      <link><xsl:call-template name="rss091.item.link" /></link>
      <description><xsl:call-template name="rss091.item.description" /></description>
    </item>
  </xsl:template>
  
</xsl:stylesheet>