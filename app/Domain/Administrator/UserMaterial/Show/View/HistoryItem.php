<?php
namespace Coyote\Domain\Administrator\UserMaterial\Show\View;

use Coyote\Domain\Administrator\View\Date;
use Coyote\Domain\Html;

class HistoryItem
{
    public function __construct(
        public ?Html   $authorMention,
        public Date    $createdAt,
        private string $type,
        public string  $badge,
        public ?string $note,
    )
    {
    }

    public function userMention(): Html|string
    {
        if ($this->authorMention === null) {
            return 'Nieznany';
        }
        return $this->authorMention;
    }

    public function icon(): string
    {
        if ($this->type === 'close-report') {
            return 'fas fa-check';
        }
        if ($this->type === 'report') {
            return 'far fa-flag';
        }
        if ($this->type === 'delete') {
            return 'far fa-trash-can';
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
        if ($this->type === 'close-report') {
            return 'zamknął raport';
        }
        return 'dodał ten';
    }
}
