<?php
namespace V3\Feature\Register;

readonly class Register
{
    public function __construct(private RegisterGate $gate)
    {
    }

    /**
     * @return RegisterError[]
     */
    public function register(
        string $login,
        string $password,
        string $passwordRepeat,
        string $email,
        bool   $termsAccepted,
    ): array
    {
        $errors = [];
        if (!$this->gate->isUsernameAvailable($login)) {
            $errors[] = RegisterError::UsernameTaken;
        }
        if (!$this->gate->isUsernameCorrect($login)) {
            $errors[] = RegisterError::UsernameMalformed;
        }
        if (!$this->gate->isEmailAvailable($password)) {
            $errors[] = RegisterError::EmailTaken;
        }
        if (\mb_strLen($password) < 3) {
            $errors[] = RegisterError::PasswordNotSecure;
        }
        if ($password !== $passwordRepeat) {
            $errors[] = RegisterError::PasswordNotRepeated;
        }
        if (!$termsAccepted) {
            $errors[] = RegisterError::TermsNotAccepted;
        }
        if (empty($errors)) {
            $this->gate->createUserAndLogin($login, $email, $password);
        }
        return $errors;
    }
}
