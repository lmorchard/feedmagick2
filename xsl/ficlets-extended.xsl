<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
    xmlns:atom="http://www.w3.org/2005/Atom"  
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:str="http://exslt.org/strings"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:html="http://www.w3.org/1999/xhtml"
    extension-element-prefixes="str">

    <xsl:output encoding="utf-8" indent="yes" method="xml"/>

    <xsl:template match="/">
        <rss version="2.0">
            <channel>
                <title><xsl:value-of select="/atom:feed/atom:title/text()" /> (Fictlets enhanced feed)</title>
                <link><xsl:value-of select="/atom:feed/atom:link[@rel='alternate']/@href" /></link>
                <description><xsl:value-of select="/atom:feed/atom:subtitle/text()" /> (Enhanced feed courtesy of http://decafbad.com/2007/04/ficlets.xsl)</description>

                <xsl:for-each select="//atom:entry[position()]">

                    <xsl:variable name="url"          select="./atom:link/@href" />
                    <xsl:variable name="title"        select="./atom:title/text()" />
                    <xsl:variable name="author_url"   select="./atom:author/atom:uri/text()" />
                    <xsl:variable name="author_name"  select="./atom:author/atom:name/text()" />
                    <xsl:variable name="description"  select="./atom:content/text()" />

                    <xsl:variable name="page"         select="document($url)" />
                    <xsl:variable name="pubdate"      select="$page//html:div[@class='story-meta']/html:p[@class='pubdate']/html:abbr/@title" />
                    <xsl:variable name="stars_img"    select="$page//html:p[@class='avg-rating']/html:img/@src" />
                    <xsl:variable name="stars"        select="substring($stars_img, 15, 3)" />

                    <item>
                        <title>[story] <xsl:value-of select="$title" /> <xsl:if test="$stars"> (<xsl:value-of select="$stars" /> stars)</xsl:if> by <xsl:value-of select="$author_name" /></title>
                        <dc:date><xsl:value-of select="$pubdate" /></dc:date>
                        <link><xsl:value-of select="$url" /></link>
                        <guid isPermaLink="false"><xsl:value-of select="$url" /></guid>
                        <description><xsl:if test="$stars">&lt;img src="http://ficlets.com<xsl:value-of select="$stars_img" />" /&gt;</xsl:if> by &lt;a href="<xsl:value-of select="$author_url" />"&gt;<xsl:value-of select="$author_name" />&lt;/a&gt;&lt;br /&gt;<xsl:value-of select="$description" /></description>
                    </item>

                    <xsl:for-each select="$page//html:div[@id='comments']/html:ol/html:li">
                        <xsl:variable name="com_url"          select="concat($url,'#',@id)" />
                        <xsl:variable name="com_title"        select="./html:h4/html:a/text()" />
                        <xsl:variable name="com_description"  select="./html:blockquote/html:p/text()" />
                        <xsl:variable name="com_author_url"   select="./html:i[@class='reviewer vcard']/html:a[@class='url fn']/@href" />
                        <xsl:variable name="com_author_name"  select="./html:i[@class='reviewer vcard']/html:a[@class='url fn']/text()" />
                        <xsl:variable name="com_stars_img"    select="./html:abbr[@class='rating']/html:img/@src" />
                        <xsl:variable name="com_stars"        select="substring($com_stars_img, 15, 3)" />
                        <xsl:variable name="com_pubdate"      select="./html:p[@class='pubdate']/html:abbr/@title" />
                        <item>
                            <title>[comment] by <xsl:value-of select="$com_author_name" /> <xsl:if test="$com_stars"> (<xsl:value-of select="$com_stars" /> stars)</xsl:if> on <xsl:value-of select="$title" /></title>
                            <dc:date><xsl:value-of select="$com_pubdate" /></dc:date>
                            <link><xsl:value-of select="$com_url" /></link>
                            <guid isPermaLink="false"><xsl:value-of select="$com_url" /></guid>
                            <description><xsl:if test="$com_stars">&lt;img src="http://ficlets.com<xsl:value-of select="$com_stars_img" />" /&gt;</xsl:if> by &lt;a href="<xsl:value-of select="$com_author_url" />"&gt;<xsl:value-of select="$com_author_name" />&lt;/a&gt;&lt;br /&gt;<xsl:value-of select="$com_description" /></description>
                        </item>
                    </xsl:for-each>

                    <xsl:for-each select="$page//html:h3[text()='Prequels']/following-sibling::html:ul/html:li">
                        <xsl:variable name="pre_url"          select="./html:h4/html:a/@href" />
                        <xsl:variable name="pre_title"        select="./html:h4/html:a/text()" />
                        <xsl:variable name="pre_author_url"   select="./html:i/html:a/@href" />
                        <xsl:variable name="pre_author_name"  select="./html:i/html:a/text()" />
                        <item>
                            <!-- TODO: Could find the date, but would need to spider another page level deep -->
                            <!-- <dc:date><xsl:value-of select="$pre_pubdate" /></dc:date> -->
                            <title>[prequel] <xsl:value-of select="$pre_title" /> by <xsl:value-of select="$pre_author_name" /></title>
                            <link><xsl:value-of select="$pre_url" /></link>
                            <guid isPermaLink="false"><xsl:value-of select="$pre_url" />#prequel</guid>
                            <description>by &lt;a href="<xsl:value-of select="$pre_author_url" />"&gt;<xsl:value-of select="$pre_author_name" />&lt;/a&gt; for &lt;a href="<xsl:value-of select="$url" />"&gt;<xsl:value-of select="$title" />&lt;/a&gt;</description>
                        </item>
                    </xsl:for-each>

                    <xsl:for-each select="$page//html:h3[text()='Sequels']/following-sibling::html:ul/html:li">
                        <xsl:variable name="seq_url"          select="./html:h4/html:a/@href" />
                        <xsl:variable name="seq_title"        select="./html:h4/html:a/text()" />
                        <xsl:variable name="seq_author_url"   select="./html:i/html:a/@href" />
                        <xsl:variable name="seq_author_name"  select="./html:i/html:a/text()" />
                        <item>
                            <!-- TODO: Could find the date, but would need to spider another page level deep -->
                            <!-- <dc:date><xsl:value-of select="$seq_pubdate" /></dc:date> -->
                            <title>[sequel] <xsl:value-of select="$seq_title" /> by <xsl:value-of select="$seq_author_name" /></title>
                            <link><xsl:value-of select="$seq_url" /></link>
                            <guid isPermaLink="false"><xsl:value-of select="$seq_url" />#sequel</guid>
                            <description>by &lt;a href="<xsl:value-of select="$seq_author_url" />"&gt;<xsl:value-of select="$seq_author_name" />&lt;/a&gt; for &lt;a href="<xsl:value-of select="$url" />"&gt;<xsl:value-of select="$title" />&lt;/a&gt;</description>
                        </item>
                    </xsl:for-each>

                </xsl:for-each>
            </channel>
        </rss>
    </xsl:template>

</xsl:stylesheet>
