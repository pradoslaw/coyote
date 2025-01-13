<?php
namespace Coyote\Services\Parser\Parsers;

use League\CommonMark\Extension\CommonMark;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

class FencedCodeRenderer implements NodeRendererInterface
{
    private CommonMark\Renderer\Block\FencedCodeRenderer $renderer;

    public function __construct()
    {
        $this->renderer = new CommonMark\Renderer\Block\FencedCodeRenderer();
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): HtmlElement
    {
        return new HtmlElement('div', ['class' => 'markdown-code'], [
            new HtmlElement('div', ['class' => 'copy-button'], ['Kopiuj']),
            $this->renderer->render($node, $childRenderer),
        ]);
    }
}
