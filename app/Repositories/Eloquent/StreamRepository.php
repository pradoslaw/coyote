<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\StreamRepositoryInterface;
use Coyote\Stream;

class StreamRepository extends Repository implements StreamRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return Stream::class;
    }

    /**
     * @inheritdoc
     */
    public function hasLoggedBefore($userId, $ip, $browser)
    {
        return $this
            ->model
            ->where('actor->id', $userId)
            ->whereNested(function ($builder) use ($ip, $browser) {
                return $builder->where('ip', $ip)->orWhere('browser', $browser);
            })
            ->exists();
    }
}
