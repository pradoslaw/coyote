<?php
namespace Neon\Domain\Event;

readonly class Event
{
    public string $key;

    public function __construct(
        public string    $title,
        public string    $city,
        public bool      $free,
        public array     $tags,
        public Date      $date,
        public EventKind $kind,
        public string    $url,
        public string    $microblogUrl,
    )
    {
        $this->key = $this->slug($this->title);
    }

    private function slug(string $string): string
    {
        $words = \explode(' ', $string);
        $capitalizedKeys = \array_map(fn(string $word) => \ucFirst(\strToLower($word)), $words);
        $camelCase = implode('', $capitalizedKeys);
        return \lcFirst($camelCase);
    }
}
