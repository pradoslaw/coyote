<?php
namespace Neon\Test\BaseFixture\Domain;

use Neon\Application;
use Neon\Domain\Visitor;
use Neon\Test\Unit\Navigation\Fixture\LoggedInUser;

class TestApplication
{
    public static function application(
        string  $name = null,
        Visitor $visitor = null,
        bool    $darkTheme = null,
    ): Application
    {
        return new Application(
            $name ?? '',
            new NoAttendance(),
            new NoJobOffers(),
            new NoEvents(),
            $visitor ?? LoggedInUser::guest(),
            new NoSystem($darkTheme ?? false));
    }
}
