<?php
namespace Neon\Domain;

readonly class Event
{
    public function __construct(
        public string    $title,
        public string    $city,
        public bool      $free,
        public array     $tags,
        public Date      $date,
        public EventKind $kind,
    )
    {
    }
}
