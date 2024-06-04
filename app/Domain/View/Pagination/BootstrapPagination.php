<?php
namespace Coyote\Domain\View\Pagination;

use Coyote\Domain\Html;

class BootstrapPagination extends Html
{
    private PageButtons $paginator;

    public function __construct(
        private int   $page,
        int           $pageSize,
        int           $total,
        private array $queryParams = [],
    )
    {
        $this->paginator = new PageButtons($page, $pageSize, $total);
    }

    protected function toHtml(): string
    {
        $html = '<ul class="pagination">';
        foreach ($this->pageButtons() as $button) {
            $html .= "<li class='page-item $button->cssClass'>";
            if ($button->isLink()) {
                $url = \http_build_query(['page' => $button->hrefPage, ...$this->queryParams]);
                $html .= "<a href='?$url' class='page-link'>$button->htmlValue</a>";
            } else {
                $html .= "<span class='page-link'>$button->htmlValue</span>";
            }
            $html .= '</li>';
        }
        return $html . '</ul>';
    }

    /**
     * @return Button[]
     */
    private function pageButtons(): iterable
    {
        if ($this->paginator->hasPrevious()) {
            yield new Button('&#xab;', $this->page - 1, '');
        }
        foreach ($this->paginator->buttons() as $page) {
            if ($page === $this->paginator->currentPage()) {
                yield new Button($page, null, 'active');
            } else if ($page === '...') {
                yield new Button($page, null, 'disabled');
            } else {
                yield new Button($page, $page, '');
            }
        }
        if ($this->paginator->hasNext()) {
            yield new Button('&#xbb;', $this->page + 1, '');
        }
    }
}
