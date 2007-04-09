<?xml version='1.0' encoding='iso-8859-1'?>
<!--
     $Id: escape-html.xsl 544 2004-12-04 05:15:20Z deusx $
     
     $Log: escape-html.xsl,v $
     Revision 1.3  2004/04/07 00:18:46  deusx
     Added angle bracket double-encoding

     Revision 1.2  2003/09/01 22:08:47  deusx
     Working on adding CVS comment headers, attribution,  and license info to files

-->

<xsl:stylesheet version='1.0' 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:uri="http://www.w3.org/2000/07/uri43/uri.xsl?template="  
  xmlns:html="http://www.w3.org/1999/xhtml"
  xmlns:dyn="http://exslt.org/dynamic"
  xmlns:str="http://exslt.org/strings"
  extension-element-prefixes="uri str dyn">

  <!-- <xsl:import href="http://www.w3.org/2000/07/uri43/uri.xsl"/> -->
  <xsl:import href="uri.xsl" />
  <xsl:import href="str.replace.template.xsl" />
  
  <!-- Links and images with relative URI munging -->
  <xsl:template mode="escape-html" match="//html:a">&lt;a href="<xsl:call-template name="uri:expand"><xsl:with-param name="there" select="./@href"/><xsl:with-param name="base" select="$base"/></xsl:call-template>"&gt;<xsl:apply-templates mode="escape-html"/>&lt;/a&gt;</xsl:template>
  
  <xsl:template mode="escape-html" match="//html:img">&lt;img src="<xsl:call-template name="uri:expand"><xsl:with-param name="there" select="./@src"/><xsl:with-param name="base" select="$base"/></xsl:call-template>" /&gt;</xsl:template>

  <!-- Formatting tags -->
  <xsl:template mode="escape-html" match="//html:b">&lt;b&gt;<xsl:apply-templates mode="escape-html"/>&lt;/b&gt;</xsl:template>
  <xsl:template mode="escape-html" match="//html:i">&lt;i&gt;<xsl:apply-templates mode="escape-html"/>&lt;/i&gt;</xsl:template>
  <xsl:template mode="escape-html" match="//html:p">&lt;p&gt;<xsl:apply-templates mode="escape-html"/>&lt;/p&gt;</xsl:template>
  
  <!-- Mangle tables into paragraphs -->
  <xsl:template mode="escape-html" match="//html:td">&lt;p&gt;<xsl:apply-templates mode="escape-html"/>&lt;/p&gt;</xsl:template>
  
  <!-- Miscellaneous -->
  <xsl:template mode="escape-html" match="//html:br">&lt;br /&gt;</xsl:template>

  <!-- Double encode angle brackets -->
  <!-- TODO: This double-replace call seems really smelly. -->
  <xsl:template mode="escape-html" match="*/text()">
    <xsl:call-template name="str:replace">
      <xsl:with-param name="string">
        <xsl:call-template name="str:replace">
          <xsl:with-param name="string" select="." />
          <xsl:with-param name="search" select="'&lt;'" />
          <xsl:with-param name="replace" select="'&amp;lt;'" />
        </xsl:call-template>        
      </xsl:with-param>
      <xsl:with-param name="search" select="'&gt;'" />
      <xsl:with-param name="replace" select="'&amp;gt;'" />
    </xsl:call-template>
  </xsl:template>
  
</xsl:stylesheet>
