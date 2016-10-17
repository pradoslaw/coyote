<?php

class TransformerTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var \Coyote\Services\Markdown\Transformer
     */
    protected $transformer;

    protected function _before()
    {
        $this->transformer = new \Coyote\Services\Markdown\Transformer();
    }

    protected function _after()
    {
    }

    // tests
    public function testParserLinks()
    {
        $this->assertEquals(
            '<a href="http://www.vogel.pascal.prv.pl">http://www.vogel.pascal.prv.pl</a>',
            $this->transformer->transform('<url>http://www.vogel.pascal.prv.pl</url>')
        );

        $this->assertEquals(
            '<a href="http://gimpiatek.w.interia.pl"> gimpiatek.w.interia.pl </a>',
            $this->transformer->transform('<url="http://gimpiatek.w.interia.pl"> gimpiatek.w.interia.pl </url>')
        );

        $this->assertEquals(
            '<a href="http://4programmers.net/Forum">kliknij to</a>',
            $this->transformer->transform('<url=http://forum.4programmers.net>kliknij to</url>')
        );

        $this->assertEquals(
            'http://4programmers.net/Forum/329566?h=ksiazka#id329566',
            $this->transformer->transform('http://forum.4programmers.net/viewtopic.php?p=329566&h=ksiazka#id329566')
        );

        $this->assertEquals(
            'http://4programmers.net/Forum/506514#id506514',
            $this->transformer->transform('http://forum.4programmers.net/viewtopic.php?p=506514#id506514')
        );

        $this->assertEquals(
            'adam@4programmers.net',
            $this->transformer->transform('<email>adam@4programmers.net</email>')
        );

        $this->assertEquals(
            '<a href="http://pl.wikipedia.org/wiki/UML">UML</a>',
            $this->transformer->transform('<wiki>UML</wiki>')
        );
    }

    public function testParsePlain()
    {
        $this->assertEquals(
            "znacznikami ''<code>'' a ''</code>''.",
            $this->transformer->transform("znacznikami ''<plain><code></plain>'' a ''<plain></code></plain>''.")
        );
    }

    public function testFixCodeTag()
    {
        $this->assertEquals(
            '<code class="cpp"> tu kod </code>',
            $this->transformer->transform("<plain><code=cpp> tu kod </code></plain>")
        );

        $this->assertEquals(
            '<code>test</code>',
            $this->transformer->transform('<code>test</code>')
        );

        $this->assertEquals(
            '<code class="cpp">test</code>',
            $this->transformer->transform('<code="C++">test</code>')
        );

        $this->assertEquals(
            "<code class=\"delphi\">//(..)\nprocedure TForm1.Button16Click(Sender: TObject);\n</code>",
            $this->transformer->transform("<delphi>//(..)\nprocedure TForm1.Button16Click(Sender: TObject);\n</delphi>")
        );
    }
}
