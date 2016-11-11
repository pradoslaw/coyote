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

        $this->assertEquals(
            "`<tt>`",
            $this->transformer->transform("<plain><tt></plain>")
        );

        $this->assertEquals(
            "`<kbd>`",
            $this->transformer->transform("<plain><kbd></plain>")
        );

        $this->assertEquals(
            "postaci \` oraz \`.",
            $this->transformer->transform("postaci <plain>`</plain> oraz <plain>''</plain>.")
        );
    }

    public function testHashImg()
    {
        $input = "wbonline.oleobject.document.write('<img src=\"C:\szajs.gif');";

        $this->assertEquals($input, $this->transformer->transform($input));
    }

    public function testDoNotParseInBacktick()
    {
        $input = '`//kursywa//` oraz `*bold*`';

        $this->assertEquals($input, $this->transformer->transform($input));
    }

    public function testFixCodeTag()
    {
        $this->assertEquals(
            "```php\nkod\n```",
            $this->transformer->transform("<code=php>\nkod\n</code>")
        );

        $this->assertEquals(
            "```php\nkod\n```",
            $this->transformer->transform("<code=php>kod\n</code>")
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

        $this->assertEquals(
            "```\nOBW LOG: Preload undefined\nObwUtils.js:452 OBW LOG: Czy systemowa = WSZYSTKIE undefined\n2ObwUtils.js:452 OBW LOG: Preload undefined\nObwUtils.js:452 OBW LOG:  Object\n```",
            $this->transformer->transform("<code>\nOBW LOG: Preload undefined\nObwUtils.js:452 OBW LOG: Czy systemowa = WSZYSTKIE undefined\n2ObwUtils.js:452 OBW LOG: Preload undefined\nObwUtils.js:452 OBW LOG:  Object\n</code>")
        );

        $this->assertEquals(
            "```java\nclass VersionRange(override val start: Version, override val endInclusive: Version) : ClosedRange<Version>, Iterable<Version> {\n\n```",
            $this->transformer->transform("<code=java>class VersionRange(override val start: Version, override val endInclusive: Version) : ClosedRange<Version>, Iterable<Version> {\n\n</code>")
        );

        $this->assertEquals(
            "```delphi\nForceDirectories('/mnt/sdcard/auta/auto1');\n```",
            $this->transformer->transform("<code=delphi>ForceDirectories('/mnt/sdcard/auta/auto1');</code>")
        );

        $this->assertEquals(
            "```\n\$stmt->bindValue(':offset', (int)\$offset, PDO::PARAM_INT);\n\$stmt->bindValue(':limit', (int)\$limit, PDO::PARAM_INT); \n```",
            $this->transformer->transform("<code>\$stmt->bindValue(':offset', (int)\$offset, PDO::PARAM_INT);\n\$stmt->bindValue(':limit', (int)\$limit, PDO::PARAM_INT); </code>")
        );

        $this->assertEquals(
            "```html\n<html>\n</html>\n```",
            $this->transformer->transform("<code=html><html>\n</html></code>")
        );

        $this->assertEquals(
            "before \n```html\n<a...\n```\n after",
            $this->transformer->transform("before <code=html><a...</code> after")
        );

        $this->assertEquals(
            "łółłóąłśąźćź \n```html\n<a...\n```\n after",
            $this->transformer->transform("łółłóąłśąźćź <code=html><a...</code> after")
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

        $this->assertEquals(
            "żółty `łyżeczka` łódź",
            $this->transformer->transform("żółty ''łyżeczka'' łódź")
        );

        $this->assertEquals(
            "łółąóśłąśą `łóąśłą` łółąóśąs",
            $this->transformer->transform("łółąóśłąśą ''łóąśłą'' łółąóśąs")
        );

        $this->assertEquals(
            "łółąóśłąśźć ''ół ó",
            $this->transformer->transform("łółąóśłąśźć ''ół ó")
        );

        $this->assertEquals(
            "znaczników `<a>`. Np.:",
            $this->transformer->transform("znaczników ''<a>''. Np.:")
        );

        $this->assertEquals(
            "ze znaczników `<a>`. Np.:\n\n`<a href=\"http://4programmers.net/Forum/Coyote/152813-linki_wewnetrzne_-_formatowanie\">kliknij tutaj</a>`",
            $this->transformer->transform("ze znaczników ''<a>''. Np.:\n\n''<a href=\"http://forum.4programmers.net/Coyote/152813-linki_wewnetrzne_-_formatowanie\">kliknij tutaj</a>''")
        );
    }

    public function testRemoveInlineImage()
    {
        $this->assertEquals(
            "![abc.jpg](//cdn.4programmers.net/uploads/attachment/abc.jpg)",
            $this->transformer->transform("{{Image:abc.jpg}}")
        );

        $this->assertEquals(
            "![abc.jpg](//cdn.4programmers.net/uploads/attachment/abc-image(180x180).jpg)",
            $this->transformer->transform("{{Image:abc.jpg|test|180}}")
        );

        $this->transformer->mapping = ['abc.zip' => '1/2'];

        $this->assertEquals(
            '[abc.zip](//4programmers.net/Download/1/2)',
            $this->transformer->transform("{{File:abc.zip}}")
        );

        $this->assertEquals(
            "{{Image:abc.jpg\n}}",
            $this->transformer->transform("{{Image:abc.jpg\n}}")
        );
    }

    public function testRemoveTtTag()
    {
        $this->assertEquals(
            '`test``test2`',
            $this->transformer->transform('<tt>test</tt><tt>test2</tt>')
        );

        $this->assertEquals(
            "`<kbd>[[C/new]]</kbd>`.",
            $this->transformer->transform("`<kbd>[[C/new]]</kbd>`.")
        );

        $this->assertEquals(
            "Daj wszystkie `<script>` pod koniec `<body>`, nie `<head>`",
            $this->transformer->transform('Daj wszystkie <kbd><script></kbd> pod koniec <kbd><body></kbd>, nie <kbd><head></kbd>')
        );

        $this->assertEquals(
            "`<kbd>`",
            $this->transformer->transform("''<kbd>''")
        );
    }

    public function testBulletList()
    {
        $this->assertEquals(
            "* one\n* two\n* three",
            $this->transformer->transform("* one\n* two\n* three")
        );

        $this->assertEquals(
            "* one\n * two\n * three\n  * four",
            $this->transformer->transform("* one\n** two\n** three\n*** four")
        );

        $this->assertEquals(
            "#one\n#two\n#three",
            $this->transformer->transform("#one\n#two\n#three")
        );

        $this->assertEquals(
            "1. one\n2. two\n3. three",
            $this->transformer->transform("# one\n# two\n# three")
        );

        $this->assertEquals(
            "1. one\n2. two\n 1. three\n 2. four\n  1. five\n 3. six\n  1. seven",
            $this->transformer->transform("# one\n# two\n## three\n## four\n### five\n## six\n### seven")
        );

        $this->assertEquals(
            "1. one\n2. two\n3. three\n\n# one",
            $this->transformer->transform("# one\n# two\n# three\n\n# one")
        );

        $this->assertEquals(
            "# 11 odcinek\nto juz",
            $this->transformer->transform("# 11 odcinek\nto juz")
        );

        $this->assertEquals(
            "Oto moja propozycja (btw: ustalilismy, ze nie bedzie juz w bazie pol typu datetime, tylko int):\n\n#\n# Struktura tabeli dla  `coyote_forum`\n#",
            $this->transformer->transform("Oto moja propozycja (btw: ustalilismy, ze nie bedzie juz w bazie pol typu datetime, tylko int):\n\n#\n# Struktura tabeli dla  `coyote_forum`\n#")
        );
    }

    public function testStyle()
    {
        $this->assertEquals(
            'italic *italic*',
            $this->transformer->transform('italic //italic//')
        );

        $this->assertEquals(
            'italic *italic**italic*',
            $this->transformer->transform('italic //italic////italic//')
        );

        $this->assertEquals(
            'italic http://italic.com//test www.//yyy// www*yyyy*',
            $this->transformer->transform('italic http://italic.com//test www.//yyy// www//yyyy//')
        );

        $this->assertEquals(
            'm<sup>2</sup> m^2 m<sub>2</sub>',
            $this->transformer->transform('m^2^ m^2 m,,2,,')
        );
    }

    public function testHeadline()
    {
        $this->assertEquals(
            '# Header1',
            $this->transformer->transform('= Header1 =')
        );

        $this->assertEquals(
            ' # Header1 #',
            $this->transformer->transform(' # Header1 #')
        );

        $this->assertEquals(
            '## Header1',
            $this->transformer->transform('== Header1 ==')
        );

        $this->assertEquals(
            '### Header1',
            $this->transformer->transform('=== Header1 ===')
        );

        $this->assertEquals(
            "### Headline\n",
            $this->transformer->transform("Headline\n~~~~~~~")
        );

        $this->assertEquals(
            'Znowu Zimny Kaczor? ~~~@ShookTea',
            $this->transformer->transform('Znowu Zimny Kaczor? ~~~@ShookTea')
        );

        $this->assertEquals(
            "Znowu Zimny Kaczor?\n~~~@ShookTea",
            $this->transformer->transform("Znowu Zimny Kaczor?\n~~~@ShookTea")
        );

        $this->assertEquals(
            "\n### Tworzenie linków\n",
            $this->transformer->transform("\nTworzenie linków\n~~~~~~~~~~~")
        );
    }

    public function testQuote()
    {
        $this->assertEquals(
            "\n> Input\ntest",
            $this->transformer->transform("<quote>Input</quote>test")
        );

        $this->assertEquals(
            "\n> Input\n",
            $this->transformer->transform("<quote>\nInput\n</quote>")
        );

        $this->assertEquals(
            "\n> One\n> Two\n",
            $this->transformer->transform("<quote>\nOne\nTwo</quote>")
        );

        $this->assertEquals(
            "Plain\n\n> One\n> Two\n\nSecond",
            $this->transformer->transform("Plain\n<quote>\nOne\nTwo</quote>\nSecond")
        );

        $this->assertEquals(
            "\n> one \n> > two\n",
            $this->transformer->transform("<quote>one \n> two</quote>")
        );

        $this->assertEquals(
            "\n> one\n> > two\n",
            $this->transformer->transform("<quote>one<quote>two</quote></quote>")
        );

        $this->assertEquals(
            "\n> one\n> > two\n> still one\n",
            $this->transformer->transform("<quote>one<quote>two</quote>still one</quote>")
        );

        $this->assertEquals(
            "\n> one\n> > two\n> > still two\n> still one\n",
            $this->transformer->transform("<quote>one<quote>two\nstill two</quote>still one</quote>")
        );

        $this->assertEquals(
            "\n> \n",
            $this->transformer->transform("<quote></quote>")
        );

        $this->assertEquals(
            "<quote>\n> \n",
            $this->transformer->transform("<quote><quote></quote>")
        );

        $this->assertEquals(
            "\n> Line 1\n> Line 2\n",
            $this->transformer->transform("<quote>Line 1\nLine 2</quote>")
        );

        $this->assertEquals(
            "> \n> Line 1\n> > Line2\n",
            $this->transformer->transform("> <quote>Line 1\n> Line2</quote>")
        );

        $this->assertEquals(
            "`<quote>Line 1Line 2</quote>`",
            $this->transformer->transform("''<quote>Line 1Line 2</quote>''")
        );

        $this->assertEquals(
            "\n> > Line 1\n> > Line 2\n",
            $this->transformer->transform("<quote><quote>Line 1\nLine 2</quote></quote>")
        );

        $this->assertEquals(
            "\n > ##### adam napisał(a)\n> test\n",
            $this->transformer->transform("<quote=adam>test</quote>")
        );

        $this->transformer->quote[1] = 'adam';

        $this->assertEquals(
            "\n > ##### [adam napisał(a)](" . route('forum.share', [1]) . "):\n> test\n",
            $this->transformer->transform("<quote=1>test</quote>")
        );

        $this->assertEquals(
            "\n > ##### [adam napisał(a)](" . route('forum.share', [1]) . "):\n> test\n",
            $this->transformer->transform('<quote="1">test</quote>')
        );

        $this->assertEquals(
            "Przyznaję się bez bicia\n> uzależnienie\n",
            $this->transformer->transform("Przyznaję się bez bicia<quote>uzależnienie</quote>")
        );

        $this->assertEquals(
            "Przyznaję się bez bicia: Jestem uzależniony.\n\n> Hmmmmm naszly mnie mysli, ze to uzaleznienie jest calkiem przyjemne, ale pod kiilkoma warunkami :)\n> \n> 1. Trzeba sie wietrzyc ! :)\n\nChodzi ci o inny nałóg?",
            $this->transformer->transform("Przyznaję się bez bicia: Jestem uzależniony.\r\n<quote>Hmmmmm naszly mnie mysli, ze to uzaleznienie jest calkiem przyjemne, ale pod kiilkoma warunkami :)\r\n\r\n1. Trzeba sie wietrzyc ! :)</quote>\r\nChodzi ci o inny nałóg?")
        );

        $this->assertEquals(
            "\n> \n```html\ntest\n```\n",
            $this->transformer->transform("<quote><code=html>test</code></quote>")
        );
    }

    public function testTable()
    {
        $this->assertEquals(
            "Nagłówek 1 | Nagłówek 2\n---------------- | ----------------\nKolumna 1 | Kolumna 2",
            $this->transformer->transform("||=Nagłówek 1||Nagłówek 2\n||Kolumna 1||Kolumna 2")
        );

        $this->assertEquals(
            "Nagłówek 1 | Nagłówek 2\n---------------- | ----------------\nKolumna 1 | Kolumna 2",
            $this->transformer->transform("||=Nagłówek 1||Nagłówek 2||\n||Kolumna 1||Kolumna 2")
        );

        $this->assertEquals(
            "Nagłówek 1 | Nagłówek 2 | Nagłówek 3\n---------------- | ---------------- | ----------------\nKolumna 1 | Kolumna 2 | Kolumna 3",
            $this->transformer->transform("||=Nagłówek 1||Nagłówek 2||Nagłówek 3\n||Kolumna 1||Kolumna 2||Kolumna 3")
        );
    }
}
