<?php
namespace Coyote\Domain\Administrator\UserMaterial\List\View;

use Coyote\Domain\Html;
use Coyote\Domain\StringHtml;
use Coyote\Services\Parser\Factories\PostFactory;

class MarkdownRender
{
    private PostFactory $postRender;

    public function __construct()
    {
        $this->postRender = app('parser.post');
    }

    public function render(string $markdown): Html
    {
        return new StringHtml($this->postRender->parse($markdown));
    }
}
