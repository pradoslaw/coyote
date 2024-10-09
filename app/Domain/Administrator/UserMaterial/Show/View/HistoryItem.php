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
            return 'logReportClosed';
        }
        if ($this->type === 'report') {
            return 'logItemReported';
        }
        if ($this->type === 'delete') {
            return 'logItemDeleted';
        }
        if ($this->type === 'create') {
            return 'logItemCreated';
        }
        throw new \Exception("No such type: $this->type");
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
