<?php
namespace Coyote\Domain\Administrator\UserMaterial\List\View;

use Coyote\Domain\Administrator\View\Date;
use Coyote\Domain\Administrator\View\Html\InlineHtml;
use Coyote\Domain\Administrator\View\Html\SubstringHtml;
use Coyote\Domain\Html;

class MaterialItem
{
    public Html $preview;

    public function __construct(
        public string  $type,
        public Date    $createdAt,
        public ?Date   $deletedAt,
        public string  $authorUsername,
        public ?string $authorImageUrl,
        public Html    $content,
        public bool    $reported,
        public bool    $reportOpen,
        public ?string $adminUrl,
    )
    {
        $this->preview = new SubstringHtml(new InlineHtml($content), 100);
    }
}
