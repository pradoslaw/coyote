<?php
namespace Coyote\Services\Reputation;

use Coyote\Repositories\Contracts\ReputationRepositoryInterface;

abstract class Reputation implements ReputationInterface
{
    protected $typeId;
    protected $reputation;
    protected $userId;
    protected $excerpt;
    protected $value;
    protected $url;
    protected $isPositive = true;
    protected $metadata = null;

    /**
     * @param ReputationRepositoryInterface $reputation
     */
    public function __construct(ReputationRepositoryInterface $reputation)
    {
        $this->reputation = $reputation;
        $this->typeId = static::ID;

        $this->value = $this->reputation->getDefaultValue($this->typeId);
    }

    /**
     * @param int $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param string $excerpt
     * @return $this
     */
    public function setExcerpt($excerpt)
    {
        $this->excerpt = $excerpt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExcerpt()
    {
        return $this->excerpt;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setPositive(bool $flag)
    {
        $this->isPositive = $flag;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPositive()
    {
        return $this->isPositive;
    }

    /**
     * @param array $args
     * @return $this
     */
    public function save(array $args = [])
    {
        if (!empty($args)) {
            foreach ($args as $arg => $value) {
                $this->{'set' . camel_case($arg)}($value);
            }
        }

        if ($this->getValue()) {
            $this->reputation->create([
                'type_id'  => $this->typeId,
                'user_id'  => $this->getUserId(),
                'value'    => $this->isPositive() ? $this->getValue() : -$this->getValue(),
                'excerpt'  => $this->getExcerpt(),
                'url'      => $this->getUrl(),
                'metadata' => $this->getMetadata(),
            ]);
        }

        return $this;
    }

    public function __call($name, $args)
    {
        if ('set' === substr($name, 0, 3)) {
            $meta = snake_case(substr($name, 3));
            $this->metadata[$meta] = $args[0];
        }

        return $this;
    }

    /**
     * @param array $metadata
     * @return $this
     */
    public function setMetadata(array $metadata)
    {
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * @return null
     */
    public function getMetadata()
    {
        return $this->metadata;
    }
}
