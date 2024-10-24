<?php
namespace V3\Web;

use V3\Feature\Register\RegisterError;
use V3\Peripheral;

class Register
{
    public static function registerView(array $errors): string
    {
        return Peripheral::renderTwig('view', [
            'stylesheetUrl'                  => Peripheral::resourceUrl('css/v3.css'),
            'logoUrl'                        => '/img/v3/logo.svg',
            'postSubmitUrl'                  => '/v3/register',
            'registerErrorUsernameTaken'     => \in_array(RegisterError::UsernameTaken, $errors),
            'registerErrorEmailTaken'        => \in_array(RegisterError::EmailTaken, $errors),
            'registerErrorPasswordNotSecure' => \in_array(RegisterError::PasswordNotSecure, $errors),
            'registerErrorTermsNotAccepted'  => \in_array(RegisterError::TermsNotAccepted, $errors),

            'registerErrorsUsername' => \array_merge(
                \in_array(RegisterError::UsernameTaken, $errors) ? ['Konto o podanej nazwie użytkownika już istnieje.'] : [],
                \in_array(RegisterError::UsernameMalformed, $errors) ? ['Nazwa użytkownika jest niepoprawna.'] : [],
            ),

            'registerErrorsPassword' =>
                \in_array(RegisterError::PasswordNotRepeated, $errors) ?
                    ['Podane hasłą nie pasują do siebie.'] : [],

        ]);
    }
}
