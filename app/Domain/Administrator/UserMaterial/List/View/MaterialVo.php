<?php
namespace Coyote\Domain\Administrator\UserMaterial\List\View;

use Coyote\Domain\Administrator\AvatarCdn;
use Coyote\Domain\Administrator\UserMaterial\List\Store\MaterialResult;
use Coyote\Domain\Administrator\UserMaterial\Material;
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
        return \array_map($this->viewObject(...), $this->materials->materials);
    }

    private function viewObject(Material $material): MaterialItem
    {
        $content = $this->render->render($material->contentMarkdown);

        $deletedAt = $material->parentDeletedAt ?? $material->deletedAt;
        return new MaterialItem(
            $this->type($material),
            $this->time->format($material->createdAt),
            $this->time->ago($material->createdAt),
            $deletedAt ? $this->time->date($deletedAt) : null,
            $material->authorUsername,
            $this->cdn->avatar($material->authorImageUrl),
            $content,
            new PostPreview((string)$content),
            $material->reported,
            $this->adminUrl($material),
        );
    }

    private function type(Material $material): string
    {
        $types = ['post' => 'post', 'comment' => 'komentarz', 'microblog' => 'mikroblog'];
        return $types[$material->type];
    }

    private function adminUrl(Material $material): ?string
    {
        if ($material->type === 'post') {
            return route('adm.flag.show.post', [$material->id]);
        }
        if ($material->type === 'comment') {
            return route('adm.flag.show.comment', [$material->id]);
        }
        return null;
    }
}
