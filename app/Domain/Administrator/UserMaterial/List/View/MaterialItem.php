<?php
namespace Coyote\Domain\Administrator\UserMaterial\List\View;

use Coyote\Domain\Administrator\View\Date;
use Coyote\Domain\Administrator\View\SubstringHtml;
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
        public ?string $adminUrl,
    )
    {
        $this->preview = new SubstringHtml($content, 100);
    }
}
