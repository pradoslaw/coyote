<?php
namespace Tests\Acceptance\AcceptanceDsl;

readonly class Driver
{
    public function __construct(private WebDriver $driver) {}

    public function closeGdpr(): void
    {
        $this->driver->navigate('/');
        $this->driver->find('#gdpr-all')->click();
    }

    public function registerUser(string $username, string $password, string $email): void
    {
        $this->driver->navigate('/Register');
        $this->driver->fillInput('input[name="name"]', $username);
        $this->driver->fillInput('input[name="password"]', $password);
        $this->driver->fillInput('input[name="password_confirmation"]', $password);
        $this->driver->fillInput('input[name="email"]', $email);
        $this->driver->selectCheckbox('input[name="terms"][type="checkbox"]');
        $this->driver->pressButton('Utwórz konto');
    }

    public function hasRegistrationConfirmation(): bool
    {
        return \in_array(
            'Konto zostało utworzone. Na podany adres e-mail, przesłany został link aktywacyjny.',
            $this->driver->currentTextNodes());
    }

    public function loginUser(string $username, string $password): void
    {
        $this->driver->navigate('/Login');
        $this->driver->fillInput('input[name="name"]', $username);
        $this->driver->fillInput('input[name="password"]', $password);
        $this->driver->pressButton('Zaloguj się');
    }

    public function readLoggedUserEmail(): string
    {
        $this->driver->navigate('/User/Settings');
        return $this->driver->readInputValue('input[name="email"]');
    }
}
