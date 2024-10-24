<?php
namespace V3\Feature\Register;

interface RegisterGate
{
    public function isUsernameAvailable(string $login): bool;

    public function isUsernameCorrect(string $login): bool;

    public function isEmailAvailable(string $password): bool;

    public function createUserAndLogin(string $login, string $email, string $password): void;
}
