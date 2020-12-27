<?php

namespace Tests\Unit\Services\Parser\Parsers\Parentheses;

use Coyote\Services\Parser\Parsers\Parentheses\SymmetricParenthesesChunks;
use PHPUnit\Framework\TestCase;

class SymmetricParenthesesChunksTest extends TestCase
{
    /**
     * @test
     * @dataProvider cases
     */
    public function shouldMakeChunks(string $input, array $expected)
    {
        // given
        $chunks = new SymmetricParenthesesChunks();

        // when
        $result = $chunks->chunk($input);

        // then
        $this->assertSame($expected, $result);
    }

    public function cases(): array
    {
        return [
            # Corner cases
            ['', []],
            ['(', ['(']],
            [')', [')']],
            ['(((', ['(', '(', '(']],
            [')))', [')', ')', ')']],

            # Regular cases
            'link'                  => ['link', ['link']],
            'link + ('              => ['link(', ['link', '(']],
            'link + (()'            => ['foo(bar(lorem)', ['foo', '(bar', '(lorem)']],
            'link()'                => ['link(path)', ['link', '(path)']],
            'link() + ('            => ['link(path)(', ['link', '(path)', '(']],
            'link() + )'            => ['link(path))', ['link', '(path)', ')']],
            'link() + ))'           => ['link()foo)bar)lorem', ['link', '()', 'foo', ')', 'bar', ')', 'lorem']],
            'link()()'              => ['link(foo)(bar)', ['link', '(foo)', '(bar)']],
            'link()() + )'          => ['link(foo)(bar))', ['link', '(foo)', '(bar)', ')']],

            # Multiple links
            'link() + ) + link()'   => ['link(foo))link(bar)', ['link', '(foo)', ')', 'link', '(bar)']],
            'link() + ( + link()'   => ['link(foo)(link(bar)', ['link', '(foo)', '(link', '(bar)']],

            # Nesting
            'link + ((()'           => ['link (((bar)', ['link ', '(', '(', '(bar)']],
            '(link)'                => ['(link)', ['(link)']],

            # Business cases
            'malformed parentheses' => ['http://4pr.net/Forum/(tex(t)', ['http://4pr.net/Forum/', '(tex', '(t)']],
            'enclosed links'        => ['http://4pr.net/Forum(https://4pr.net/Forum', ['http://4pr.net/Forum', '(https://4pr.net/Forum']],

            # Unhappy consequence
            // These examples are partially valid links, but are split into chunks. This is inconvenient, but
            // is a necessary consequence of a linear algorithm. At the second iteration (`(f`), we don't know
            // if the last chunk will be `)` or `(`? So we can't assume the chunk will contain a valid expression,
            // or not. We'll assume it's an invalid chunk, and let ParenthesesParser to handle it.
            'link(())() + )'        => ['link(f(oo))(bar))', ['link', '(f', '(oo))', '(bar)', ')']],
            'link((()))() + )'      => ['link(f(o(o)))(bar))', ['link', '(f', '(o', '(o)))', '(bar)', ')']],
            'link(()) + ( + link()' => ['link((foo)) (link(bar)', ['link', '(', '(foo))', ' ', '(link', '(bar)']],
        ];
    }
}
