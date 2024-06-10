<?php
namespace Coyote\Domain\Administrator\UserMaterial\View;

use Carbon\Carbon;
use Coyote\Domain\Administrator\AvatarCdn;
use Coyote\Domain\Administrator\UserMaterial\Material;
use Coyote\Domain\Administrator\UserMaterial\Store\MaterialResult;
use Coyote\Domain\Administrator\View\PostPreview;

readonly class MaterialVo
{
    public function __construct(
        private MarkdownRender $render,
        private Time           $time,
        private MaterialResult $materials,
        private AvatarCdn      $cdn,
    )
    {
    }

    public function total(): int
    {
        return $this->materials->total;
    }

    public function items(): array
    {
        $items = [];
        foreach ($this->materials->materials as $material) {
            $items[] = $this->viewObject($material);
        }
        return $items;
    }

    private function viewObject(Material $material): MaterialItem
    {
        $content = $this->render->render($material->contentMarkdown);

        return new MaterialItem(
            $this->type($material),
            $this->time->format($material->createdAt),
            $this->time->ago($material->createdAt),
            $material->deletedAt !== null,
            $this->time->format($material->deletedAt ?? new Carbon()),
            $this->time->ago($material->deletedAt ?? new Carbon()),
            $material->authorUsername,
            $this->cdn->avatar($material->authorImageUrl),
            $content,
            new PostPreview((string)$content),
            $material->reported,
        );
    }

    private function type(Material $material): string
    {
        $types = ['post' => 'post', 'comment' => 'komentarz', 'microblog' => 'mikroblog'];
        return $types[$material->type];
    }
}
