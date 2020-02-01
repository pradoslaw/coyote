<?php

namespace Tests;

use Coyote\Group;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}
