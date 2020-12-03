<?php

namespace Coyote\Rules;

use Coyote\Repositories\Contracts\TagRepositoryInterface;
use Coyote\Repositories\Criteria\WithTrashed;
use Illuminate\Contracts\Validation\Rule;

class TagDeleted implements Rule
{
    private TagRepositoryInterface $repository;
    private string $value;

    public function __construct(TagRepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->repository->pushCriteria(new WithTrashed());
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->value = $value;
        $result = $this->repository->findBy('name', $value, ['id', 'deleted_at']);

        if (!$result) {
            return true;
        }

        return null === $result->deleted_at;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.tag_deleted', ['value' => $this->value]);
    }
}
