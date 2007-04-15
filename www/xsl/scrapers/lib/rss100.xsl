<?xml version='1.0' encoding='iso-8859-1'?>
<!--
     $Id: rss100.xsl 544 2004-12-04 05:15:20Z deusx $
     
     $Log: rss100.xsl,v $
     Revision 1.4  2004/02/05 14:41:04  deusx
     Removed some superfluous namespaces, added generator agent info

     Revision 1.3  2004/02/05 14:40:12  deusx
     Removed some superfluous namespaces, added generator agent info

     Revision 1.2  2003/09/01 22:08:47  deusx
     Working on adding CVS comment headers, attribution,  and license info to files

-->

<xsl:stylesheet version='1.0' 
  xmlns:xsl='http://www.w3.org/1999/XSL/Transform'
  xmlns:html="http://www.w3.org/1999/xhtml"
  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns:dyn="http://exslt.org/dynamic"
  extension-element-prefixes="dyn">
  
  <!-- RSS v1.00 -->

  <xsl:template name="rss100.title"><xsl:call-template name="title" /></xsl:template>
  <xsl:template name="rss100.link"><xsl:call-template name="link" /></xsl:template>
  <xsl:template name="rss100.description"><xsl:call-template name="description" /></xsl:template>
  <xsl:template name="rss100.item.link"><xsl:call-template name="item.link" /></xsl:template>
  <xsl:template name="rss100.item.title"><xsl:call-template name="item.title" /></xsl:template>
  <xsl:template name="rss100.item.description"><xsl:call-template name="item.description" /></xsl:template>
  
  <xsl:template name="rss100">
    <rdf:RDF 
      xmlns="http://purl.org/rss/1.0/"
      xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" 
      xmlns:dc="http://purl.org/dc/elements/1.1/"
      xmlns:admin="http://webns.net/mvcb/">
      
      <channel rdf:about="http://www.decafbad.com/blog">
        <xsl:attribute name="rdf:about">
          <xsl:call-template name="rss100.link" />
        </xsl:attribute>
        <title><xsl:call-template name="rss100.title" /></title>
        <link><xsl:call-template name="rss100.link" /></link>
        <description><xsl:call-template name="rss100.description" /></description>
        <language><xsl:value-of select="$language" /></language>
        <admin:generatorAgent rdf:resource="http://www.decafbad.com/twiki/bin/view/Main/XslScraper" />
        <admin:errorReportsTo rdf:resource="mailto:deus_x@pobox.com"/>
        <!--
        I don't own scraped sites... so the following is likely inappropriate:
        <dc:creator>Leslie Michael Orchard (deus_x@pobox.com)</dc:creator>
        <dc:rights>Copyright Leslie Michael Orchard</dc:rights>
        -->
        
        <items>
          <rdf:Seq>
            <xsl:choose> 
              <xsl:when test="$items.order='reverse'">
                <xsl:for-each select="dyn:evaluate($items.path)">
                  <xsl:sort select="position()" order="descending" data-type="number" />
                  <xsl:if test="position() &lt;= $maxItems">            
                    <xsl:call-template name="rss100.seqItem" />
                  </xsl:if>
                </xsl:for-each>            
              </xsl:when>
              <xsl:otherwise>
                <xsl:for-each select="dyn:evaluate($items.path)">
                  <xsl:if test="position() &lt;= $maxItems">            
                    <xsl:call-template name="rss100.seqItem" />
                  </xsl:if>
                </xsl:for-each>            
              </xsl:otherwise>
            </xsl:choose>
            
          </rdf:Seq>
        </items>
        
      </channel>
      
      <xsl:choose> 
        <xsl:when test="$items.order='reverse'">
          <xsl:for-each select="dyn:evaluate($items.path)">
            <xsl:sort select="position()" order="descending" data-type="number" />
            <xsl:if test="position() &lt;= $maxItems">            
              <xsl:call-template name="rss100.item" />
            </xsl:if>
          </xsl:for-each>
        </xsl:when>
        <xsl:otherwise>
          <xsl:for-each select="dyn:evaluate($items.path)">
            <xsl:if test="position() &lt;= $maxItems">
              <xsl:call-template name="rss100.item" />
            </xsl:if>
          </xsl:for-each>
        </xsl:otherwise>
      </xsl:choose>
      
    </rdf:RDF>

  </xsl:template>

  <xsl:template name="rss100.seqItem">
    <rdf:li><xsl:attribute name="rdf:resource">
        <xsl:call-template name="rss100.item.link" />
      </xsl:attribute></rdf:li>
  </xsl:template>

  <xsl:template name="rss100.item">
    <item xmlns="http://purl.org/rss/1.0/">
      <xsl:attribute name="rdf:about">
        <xsl:call-template name="rss100.item.link" />
      </xsl:attribute>
      <title><xsl:call-template name="rss100.item.title" /></title>
      <link><xsl:call-template name="rss100.item.link" /></link>
      <description><xsl:call-template name="rss100.item.description" /></description>
    </item>
  </xsl:template>
  
 </xsl:stylesheet>
