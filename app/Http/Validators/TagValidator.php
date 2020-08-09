<?php

namespace Coyote\Http\Validators;

use Coyote\Repositories\Contracts\TagRepositoryInterface as TagRepository;
use Illuminate\Contracts\Auth\Guard;

class TagValidator
{
    const REGEXP = '^[a-ząęśżźćółń0-9\-\.#,\+]+$';

    /**
     * @var Guard
     */
    protected $auth;

    /**
     * @var TagRepository
     */
    protected $tag;

    /**
     * @param Guard $auth
     * @param TagRepository $tag
     */
    public function __construct(Guard $auth, TagRepository $tag)
    {
        $this->auth = $auth;
        $this->tag = $tag;
    }

    /**
     * @param mixed $attribute
     * @param mixed $value
     * @return bool
     */
    public function validateTag($attribute, $value)
    {
        return pattern(self::REGEXP)->test(trim($value));
    }

    /**
     * @param mixed $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    public function validateTagCreation($attribute, $value, $parameters)
    {
        $requiredReputation = $parameters[0]; // required reputation points
        $userReputation = $this->auth->guest() ? 0 : $this->auth->user()->reputation;

        if ($userReputation >= $requiredReputation) {
            return true;
        }

        return $this->tag->where('name', $value)->value('id') !== null;
    }
}
