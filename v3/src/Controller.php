<?php
namespace V3;

use V3\Feature\Register\Register;
use V3\Feature\Register\RegisterError;

class Controller
{
    public function setupEndpoints(): void
    {
        Peripheral::addGetRoute('/v3/register', function () {
            return Web\Register::registerView([
                RegisterError::UsernameTaken,
                RegisterError::UsernameMalformed,
                RegisterError::EmailTaken,
                RegisterError::PasswordNotSecure,
                RegisterError::PasswordNotRepeated,
                RegisterError::TermsNotAccepted,
            ]);
        });
        Peripheral::addPostRoute('/v3/register', function () {
            $register = new Register(new Peripheral());
            return Web\Register::registerView($register->register(
                Peripheral::httpRequestField('registerLogin'),
                Peripheral::httpRequestField('registerPassword'),
                Peripheral::httpRequestField('registerPasswordRepeat'),
                Peripheral::httpRequestField('registerEmail'),
                Peripheral::httpRequestField('registerTermsAccepted'),
            ));
        });
    }
}
