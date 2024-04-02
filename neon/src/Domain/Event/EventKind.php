<?php
namespace Neon\Domain\Event;

enum EventKind
{
    case Workshop;
    case Conference;
    case Hackaton;
}
