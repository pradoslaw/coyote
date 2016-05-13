<?php

namespace Coyote\Http\Validators;

use Coyote\Repositories\Contracts\TagRepositoryInterface as TagRepository;
use Illuminate\Contracts\Auth\Guard;

class TagValidator
{
    const REGEXP = '[a-ząęśżźćółń0-9\-\.#,\+]';

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
     * @param array $parameters
     * @param \Illuminate\Validation\Validator $validator
     * @return bool
     */
    public function validateTag($attribute, $value, $parameters, $validator)
    {
        return preg_match('/' . self::REGEXP . '/', trim($value));
    }

    /**
     * @param mixed $attribute
     * @param mixed $value
     * @param array $parameters
     * @param \Illuminate\Validation\Validator $validator
     * @return bool
     */
    public function validateTagCreation($attribute, $value, $parameters, $validator)
    {
        $requiredReputation = $parameters[0]; // required reputation points
        $userReputation = $this->auth->guest() ? 0 : $this->auth->user()->reputation;

        if ($userReputation >= $requiredReputation) {
            return true;
        }

        $tag = $this->tag->where('name', $value)->value('id');
        if (!is_null($tag)) {
            return true;
        }

        return false;
    }
}
