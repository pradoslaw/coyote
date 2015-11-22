<?php

namespace Coyote\Repositories\Contracts;

interface ReputationRepositoryInterface extends RepositoryInterface
{
    /**
     * Zwraca domyslna ilosc pkt reputacji przepisana do danego zdarzenia (glosowanie, nowy wpis itp)
     *
     * @param int $typeId
     * @return mixed
     */
    public function getDefaultValue($typeId);
}
