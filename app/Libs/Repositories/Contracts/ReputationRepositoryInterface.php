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

    /**
     * Gets total reputation ranking
     *
     * @param int $limit
     * @return mixed
     */
    public function total($limit = 3);

    /**
     * Gets monthly reputation ranking
     *
     * @param int $limit
     * @return mixed
     */
    public function monthly($limit = 3);

    /**
     * Gets yearly reputation ranking
     *
     * @param int $limit
     * @return mixed
     */
    public function yearly($limit = 3);
}
