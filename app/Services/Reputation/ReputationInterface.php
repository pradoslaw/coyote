<?php

namespace Coyote\Services\Reputation;

/**
 * Interface ReputationInterface
 */
interface ReputationInterface
{
    /**
     * @param int $userId
     * @return mixed
     */
    public function setUserId($userId);

    /**
     * @return int
     */
    public function getUserId();

    /**
     * @param string $excerpt
     * @return mixed
     */
    public function setExcerpt($excerpt);

    /**
     * @return string
     */
    public function getExcerpt();

    /**
     * @param int $value
     * @return mixed
     */
    public function setValue($value);

    /**
     * @return int
     */
    public function getValue();

    /**
     * @param string $url
     * @return mixed
     */
    public function setUrl($url);

    /**
     * @return string
     */
    public function getUrl();

    /**
     * @param bool $flag
     * @return mixed
     */
    public function setIsPositive($flag);

    /**
     * @return bool
     */
    public function isPositive();

    /**
     * @param array $args
     * @return mixed
     */
    public function save(array $args = []);

    /**
     * @param array $metadata
     * @return mixed
     */
    public function setMetadata(array $metadata);

    /**
     * @return array
     */
    public function getMetadata();
}
