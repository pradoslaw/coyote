<?php
namespace Coyote\Domain\Administrator\UserMaterial\Show\View;

use Coyote\Domain\Administrator\View\Date;
use Coyote\Domain\Administrator\View\Mention;

class HistoryItem
{
    public function __construct(
        public Mention $authorMention,
        public Date    $createdAt,
        public string  $type,
        public string  $kind,
        public ?string $note,
    )
    {
    }

    public function icon(): string
    {
        if ($this->type === 'report') {
            return 'far fa-flag';
        }
        return 'far fa-comment';
    }

    public function actionVerbPastTense(): string
    {
        if ($this->type === 'report') {
            return 'zgłosił';
        }
        return 'dodał';
    }
}
