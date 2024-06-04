<?php
namespace Coyote\Domain\View\Pagination;

readonly class PageButtons
{
    public function __construct(
        private int $page,
        private int $pageSize,
        private int $total,
    )
    {
        if ($total < 0) {
            throw new \InvalidArgumentException("Negative total: $total");
        }
        if ($this->pageSize < 1) {
            throw new \InvalidArgumentException("Invalid page size: $pageSize");
        }
    }

    public function currentPage(): int
    {
        return \max($this->firstPage(), $this->page);
    }

    private function firstPage(): int
    {
        return 1;
    }

    public function lastPage(): int
    {
        return \max(\ceil($this->total / $this->pageSize), 1);
    }

    public function hasPrevious(): bool
    {
        return $this->page > $this->firstPage();
    }

    public function hasNext(): bool
    {
        return $this->page < $this->lastPage();
    }

    public function buttons(): array
    {
        if ($this->lastPage() === 1) {
            return [];
        }
        [$start, $end] = $this->paddedRegion(5, 2);
        $buttons = range($start, $end);
        if ($start > $this->firstPage()) {
            \array_unshift($buttons, 1, 2, '...');
        }
        if ($end < $this->lastPage()) {
            \array_push($buttons, '...', $this->lastPage() - 1, $this->lastPage());
        }
        return $buttons;
    }

    private function paddedRegion(int $spread, int $padding): array
    {
        [$start, $end] = $this->middleRegion($spread, $padding);
        if ($start === $this->firstPage() + $padding + 1) {
            $start = $this->firstPage();
        }
        if ($end === $this->lastPage() - $padding - 1) {
            $end = $this->lastPage();
        }
        return [$start, $end];
    }

    private function middleRegion(int $spread, int $padding): array
    {
        $center = $this->regionCenter($spread);
        return [
            max($this->firstPage(), $center - $padding),
            min($this->lastPage(), $center + $padding),
        ];
    }

    private function regionCenter(int $spread): int
    {
        $currentPage = \min(\max($this->firstPage(), $this->page), $this->lastPage());
        $page = max($currentPage, $this->firstPage() + $spread);
        return min($page, $this->lastPage() - $spread);
    }
}
