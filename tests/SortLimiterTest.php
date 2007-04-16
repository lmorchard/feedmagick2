<?php
require_once 'lib/FeedMagick2Test.php';
 
class SortLimiterTest extends FeedMagick2Test { 
    
    protected function setUp() {
        parent::setUp();
        $this->data = file_get_contents($this->xml_dir."/delicious-rss10.xml");
    }

    public function testSortAsc() {

        list($pipe, $headers, $body, $doc, $xpath) = $this->runPipeline(
            array(
                'sortby'    => './rss10:title',
                'sortorder' => 'asc'
            ),
            array(
                '365 tomorrows',
                'Adactio: Journal - Machine Tags of Loving Grace',
                'Amazon Web Services Blog: MySQL Interface to Amazon S3',
                'Blog for fzort - More GNU and FSF News',
                'Bunny - woo-wee woooooo. weee woooo. woo woo woo. woo wo wo. - by anne',
                'Chris Dent, 2007-04-10 / Socialtext Open Source Wiki',
                'Developing Intelligence : 10 Important Differences Between Brains and Computers',
                'Digital Web Magazine - HTML5, XHTML2, and the Future of the Web',
                'Dustin Long',
                'Fido'
            )
        );

    }

    public function testSortDesc() {

        list($pipe, $headers, $body, $doc, $xpath) = $this->runPipeline(
            array(
                'sortby'    => './rss10:title',
                'sortorder' => 'desc'
            ),
            array(
                'warrenellis.com',
                'rakaz - Make your pages load faster by combining and compressing javascript and css files',
                'modwsgi - Google Code',
                'machine tags',
                'jquery ^ 2',
                'Writing Tests [PHP-QAT: Quality Assurance Team]',
                'Wordpress OpenID Plugin+ at willnorris.com',
                'When did America become a nation of frightened wimps? | steve-olson.com',
                'The prospect of all-female conception',
                'TOKYOMANGO: Video: Cosplayer Off-Kai Ends In Police Raid'
            )
        );

    }

    public function testLimit() {

        list($pipe, $headers, $body, $doc, $xpath) = $this->runPipeline(
            array(
                'sortby'    => './rss10:title',
                'sortorder' => 'asc',
                'limit'     => 4
            ),
            array(
                '365 tomorrows',
                'Adactio: Journal - Machine Tags of Loving Grace',
                'Amazon Web Services Blog: MySQL Interface to Amazon S3',
                'Blog for fzort - More GNU and FSF News'
            )
        );

        $this->assertTrue(
            ($count = $xpath->query("//rss10:item")->length) == 4,
            "Feed should only have 4 items in it, had $count"
        );

    }

    public function testOffset() {

        list($pipe, $headers, $body, $doc, $xpath) = $this->runPipeline(
            array(
                'sortby'    => './rss10:title',
                'sortorder' => 'asc',
                'offset'    => 5
            ),
            array(
                'Chris Dent, 2007-04-10 / Socialtext Open Source Wiki',
                'Developing Intelligence : 10 Important Differences Between Brains and Computers',
                'Digital Web Magazine - HTML5, XHTML2, and the Future of the Web',
                'Dustin Long',
                'Fido'
            )
        );

    }

    public function testEverything() {

        list($pipe, $headers, $body, $doc, $xpath) = $this->runPipeline(
            array(
                'sortby'    => './rss10:title',
                'sortorder' => 'desc',
                'offset'    => '4',
                'limit'     => '5'
            ),
            array(
                'jquery ^ 2',
                'Writing Tests [PHP-QAT: Quality Assurance Team]',
                'Wordpress OpenID Plugin+ at willnorris.com',
                'When did America become a nation of frightened wimps? | steve-olson.com',
                'The prospect of all-female conception',
            )
        );

        $this->assertTrue(
            ($count = $xpath->query("//rss10:item")->length) == 5,
            "Feed should have 5 items in it, had $count"
        );

    }

    private function runPipeline($opts, $titles) {

        $pipe = new FeedMagick2_Pipeline(
            $this->feedmagick2, 'main', array(
                array(
                    'module' => 'RawLiteral', 
                    'parameters' => array( 'body' => $this->data )
                ),
                array(
                    'module' => 'SortLimiter',
                    'parameters' => $opts
                )
            )
        );

        list($headers, $body) = $pipe->fetchOutput_Raw();
        $doc = DOMDocument::loadXML($body);
        $xpath = $this->buildXPath($doc);

        $pos = 1;
        foreach ($titles as $title) {
            $this->assertTrue(
                $xpath->query("//rss10:item[".($pos)."]/rss10:title[contains(text(),'$title')]")->length > 0,
                "Title #$pos should contain '$title'"
            );
            $pos++;
        }

        return array($pipe, $headers, $body, $doc, $xpath);
    }

}
?>
