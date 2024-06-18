<?php
namespace Coyote\Domain\Administrator\User\Store;

class ReportReason
{
    public function __construct(public string $reason, public int $count)
    {
    }
}
