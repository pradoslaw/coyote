<?php
namespace Tests\Acceptance\AcceptanceDsl;

class Dsl
{
    public Driver $driver;
    private ?string $id = null;

    public function __construct(WebDriver $web)
    {
        $this->driver = new Driver($web);
    }

    public function registerUser(
        ?string $username = 'John',
        ?string $email = 'john@doe',
    ): void
    {
        $this->id = \uniqId();
        $this->driver->registerUser($username . $this->id, 'passwd', $email . $this->id);
    }

    public function loginUser(string $username): void
    {
        $this->driver->loginUser($username . $this->id, 'passwd');
    }

    public function readLoggedUserEmail(): string
    {
        return \str_replace($this->id, '', $this->driver->readLoggedUserEmail());
    }
}
