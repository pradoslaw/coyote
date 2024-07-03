<?php
namespace Coyote\Domain\Administrator\View;

use Coyote\Domain\Html;

class Mention extends Html
{
    public function __construct(private int $userId, private string $userName)
    {
    }

    protected function toHtml(): string
    {
        $url = route('adm.users.show', [$this->userId]);
        return '<a class="mention" href="' . \htmlSpecialChars($url) . '">' . '@' . $this->userName . '</a>';
    }
}
