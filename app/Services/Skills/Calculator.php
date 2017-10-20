<?php

namespace Coyote\Services\Skills;

class Calculator
{
    /**
     * @var array
     */
    protected $tags = [];

    /**
     * @param array|string $interests
     */
    public function __construct($interests)
    {
        if (is_string($interests)) {
            $interests = json_decode($interests, true);
        }

        if (!empty($interests['tags'])) {
            $this->tags = $interests['tags'];
        }
    }

    /**
     * @param string|array $tags
     */
    public function increment($tags)
    {
        $tags = is_string($tags) ? [$tags] : $tags;

        foreach ($tags as $tag) {
            if (!isset($this->tags[$tag])) {
                $this->tags[$tag] = 0;
            }

            $this->tags[$tag]++;
        }
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        if (empty($this->tags)) {
            return [];
        }

        arsort($this->tags);

        return [
            'tags'  => $this->tags,
            'ratio' => $this->ratio()
        ];
    }

    /**
     * @return string
     */
    public function toJson(): string
    {
        if (empty($this->tags)) {
            return '[]';
        }

        return json_encode($this->toArray());
    }

    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * @return array
     */
    protected function ratio(): array
    {
        $key = key($this->tags);
        $max = $this->tags[$key];

        $ratio = [];

        foreach ($this->tags as $tag => $count) {
            $ratio[$tag] = $count / $max;
        }

        return $ratio;
    }
}
