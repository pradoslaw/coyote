<?php
namespace Coyote\Domain\Administrator\UserMaterial\Show\View;

use Coyote\Domain\Administrator\View\Date;
use Coyote\Domain\Administrator\View\Mention;

class HistoryItem
{
    public function __construct(
        public ?Mention $authorMention,
        public Date     $createdAt,
        public string   $type,
        public string   $kind,
        public ?string  $note,
    )
    {
    }

    public function userMention(): Mention|string
    {
        if ($this->authorMention === null) {
            return 'Nieznany';
        }
        return $this->authorMention;
    }

    public function icon(): string
    {
        if ($this->type === 'report') {
            return 'far fa-flag';
        }
        if ($this->type === 'delete') {
            return 'far fa-trash-alt';
        }
        return 'far fa-comment';
    }

    public function actionVerbPastTense(): string
    {
        if ($this->type === 'report') {
            return 'zgłosił tą treść z powodu';
        }
        if ($this->type === 'delete') {
            return 'usunął ten';
        }
        return 'dodał ten';
    }
}
