<?php

namespace Coyote\Rules;

use Coyote\Repositories\Contracts\TagRepositoryInterface;
use Coyote\Repositories\Criteria\WithTrashed;
use Illuminate\Contracts\Validation\Rule;

class TagDeleted implements Rule
{
    private TagRepositoryInterface $repository;
    private string $tagName;

    public function __construct(TagRepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->repository->pushCriteria(new WithTrashed());
    }

    public function passes($attribute, $value): bool
    {
        $this->tagName = $value;
        $tag = $this->repository->findBy('name', $value, ['id', 'deleted_at']);
        if ($tag) {
            return null === $tag->deleted_at;
        }
        return true;
    }

    public function message(): string
    {
        return \trans('validation.tag_deleted', ['value' => $this->tagName]);
    }
}
