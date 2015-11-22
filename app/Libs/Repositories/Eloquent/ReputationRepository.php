<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\ReputationRepositoryInterface;
use Coyote\Reputation\Type;

/**
 * Class ReputationRepository
 * @package Coyote\Repositories\Eloquent
 */
class ReputationRepository extends Repository implements ReputationRepositoryInterface
{
    public function model()
    {
        return 'Coyote\Reputation';
    }

    /**
     * @inheritdoc
     */
    public function getDefaultValue($typeId)
    {
        return Type::find($typeId, ['points'])->pluck('points');
    }
}
