<?php
namespace Coyote\Domain\Administrator\Report;

use Coyote\Domain\Administrator\View\Date;
use Coyote\Domain\Administrator\View\Mention;

class Report
{
    public Mention $reporterMention;

    public function __construct(
        public int     $reporterId,
        public string  $reporterName,
        public string  $reportType,
        public ?string $reportNote,
        public Date    $reportedAt,
    )
    {
        $this->reporterMention = new Mention($this->reporterId, $this->reporterName);
    }

    public function reportedAgo(): string
    {
        return $this->reportedAt->timeAgo();
    }
}
