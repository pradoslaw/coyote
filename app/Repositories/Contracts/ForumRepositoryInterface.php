<?php

namespace Coyote\Repositories\Contracts;

interface ForumRepositoryInterface extends RepositoryInterface
{
    /**
     * Gets categories grouped by sections.
     *
     * @param string $guestId
     * @param null|int $parentId
     * @return mixed
     */
    public function categories($guestId, $parentId = null);

    /**
     * @param int $userId
     * @param array $data
     */
    public function setup($userId, array $data);

    /**
     * @param int $userId
     */
    public function deleteSetup($userId);

    /**
     * @param int|null $userId
     * @return array
     */
    public function findHiddenIds($userId);

    /**
     * Sort a list of categories that can be shown in a <select>
     *
     * @return \Coyote\Forum[]
     */
    public function list();

    /**
     * @return array
     * @deprecated
     */
    public function getTagsCloud();

    /**
     * @param int $forumId
     * @return string[]
     */
    public function popularTags(int $forumId);
}
