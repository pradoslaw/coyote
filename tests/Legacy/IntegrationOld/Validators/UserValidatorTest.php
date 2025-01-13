<?php

namespace Tests\Legacy\IntegrationOld\Validators;

use Coyote\Http\Validators\UserValidator;
use Coyote\Repositories\Contracts\UserRepositoryInterface;
use Tests\Legacy\IntegrationOld\TestCase;

class UserValidatorTest extends TestCase
{
    public function testValidateUsername()
    {
        $validator = new UserValidator($this->app[UserRepositoryInterface::class]);

        $this->assertFalse($validator->validateName(null, '꧁Oziaka꧂'));
        $this->assertTrue($validator->validateName(null, 'Jan Kowalski'));
        $this->assertTrue($validator->validateName(null, 'Jerzy Brzęszczyszykiewicz'));
    }
}
