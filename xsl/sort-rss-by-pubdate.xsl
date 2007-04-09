<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:str="http://exslt.org/strings"
    xmlns:exsl="http://exslt.org/common"
    extension-element-prefixes="str exsl">

    <xsl:template match="@* | node()">
        <xsl:copy><xsl:apply-templates select="@* | node()" /></xsl:copy>
    </xsl:template>

    <xsl:template match="channel">
        <xsl:variable name="monthShortNames" select="'Jan,Feb,Mar,Apr,May,Jun,Jul,Aug,Sep,Oct,Nov,Dec'"/>
        <xsl:variable name="shortNames" select="str:tokenize($monthShortNames,',')"/>
        <channel>
            <xsl:for-each select="@* | node()">
                <xsl:if test="not(name()='item')">
                    <xsl:copy><xsl:apply-templates select="@* | node()" /></xsl:copy>
                </xsl:if>
            </xsl:for-each>
            <xsl:for-each select="item">
                <!-- Based on http://kpumuk.info/xslt/sorting-rss-feed-by-date-using-xslt/ -->
                <xsl:sort select="substring(substring-after(substring-after(substring-after(pubDate, ' '), ' '), ' '), 1, 4)" order="descending"/>
                <xsl:sort select="count($shortNames[text()=( substring(substring-after(substring-after(current()/pubDate, ' '), ' '), 1, 3) )]/preceding-sibling::*)" order="descending" />
                <xsl:sort select="substring(substring-after(pubDate, ' '), 1, 2)" data-type="number" order="descending"/>
                <xsl:sort select="substring(substring-after(substring-after(substring-after(substring-after(pubDate, ' '), ' '), ' '), ' '), 1, 8)" data-type="text" order="descending"/>
                <xsl:copy><xsl:apply-templates select="@* | node()" /></xsl:copy>
            </xsl:for-each>
        </channel>
    </xsl:template>

</xsl:stylesheet>
