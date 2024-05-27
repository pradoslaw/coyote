<?php
namespace Coyote\Domain;

class PostStatistic
{
    public function __construct(
        public int $all,
        public int $deletedBySelf,
        public int $deletedByModerator,
    )
    {
    }

    public function deletedBySelfProportion(): string
    {
        return $this->percentage($this->deletedBySelf);
    }

    public function deletedByModeratorProportion(): string
    {
        return $this->percentage($this->deletedByModerator);
    }

    private function percentage(int $deletedBySelf): string
    {
        if ($deletedBySelf === 0) {
            return '';
        }
        $percents = 100.0 * $deletedBySelf / $this->all;
        $number = \number_format($percents, 2, '.', '');
        return "(~$number%)";
    }
}
