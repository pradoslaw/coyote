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
            "znacznikami `<code>` a `</code>`.",
            $this->transformer->transform("znacznikami ''<plain><code></plain>'' a ''<plain></code></plain>''.")
        );

        $this->assertEquals(
            "`<code>`",
            $this->transformer->transform("`<plain><code></plain>`")
        );

        $this->assertEquals(
            '`<code>`',
            $this->transformer->transform('<plain><code></plain>')
        );

        //
        $this->assertEquals(
            '`<code>``<code>`',
            $this->transformer->transform('<plain><code></plain><plain><code></plain>')
        );
    }

    public function testFixCodeTag()
    {
        $this->assertEquals(
            "```php\nkod\n```",
            $this->transformer->transform("<code=php>\nkod\n</code>")
        );

        $this->assertEquals(
            "<code class=\"php\">kod</code>",
            $this->transformer->transform("<code=php>kod</code>")
        );

        $this->assertEquals(
            '`<code class="cpp"> tu kod </code>`',
            $this->transformer->transform("<plain><code=cpp> tu kod </code></plain>")
        );

        $this->assertEquals(
            '`test`',
            $this->transformer->transform('<code>test</code>')
        );

        $this->assertEquals(
            "```cpp\ntest\n```",
            $this->transformer->transform("<code=\"C++\">\ntest\n</code>")
        );

        $this->assertEquals(
            "```delphi\n//(..)\nprocedure TForm1.Button16Click(Sender: TObject);\n```",
            $this->transformer->transform("<delphi>\n//(..)\nprocedure TForm1.Button16Click(Sender: TObject);\n</delphi>")
        );

        $this->assertEquals(
            "```python\ntest\n```",
            $this->transformer->transform("<code=python:noframe>\ntest\n</code>")
        );
    }

    public function testFixDoubleApostrophe()
    {
        $this->assertEquals(
            "musisz uzyc podwojnego apostrofu, o tak: `''`",
            $this->transformer->transform("musisz uzyc podwojnego apostrofu, o tak: `''`")
        );

        $this->assertEquals(
            "'' foobar",
            $this->transformer->transform("'' foobar")
        );

        $this->assertEquals(
            "`test`",
            $this->transformer->transform("''test''")
        );

        $this->assertEquals(
            "`backtick` oraz `backtick2`",
            $this->transformer->transform("''backtick'' oraz ''backtick2''")
        );

        $this->assertEquals(
            "`backtick``backtick2`",
            $this->transformer->transform("''backtick''''backtick2''")
        );

        $this->assertEquals(
            "``` oraz ```",
            $this->transformer->transform("''`'' oraz ''`''")
        );

        $this->assertEquals(
            "znacznikach `''<kod html>''` czy ``<kod html>``,",
            $this->transformer->transform("znacznikach <plain>''<kod html>''</plain> czy <plain>`<kod html>`</plain>,")
        );
    }
}
