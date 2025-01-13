<?php
namespace Tests\Legacy\IntegrationNew\Job;

use PHPUnit\Framework\TestCase;
use Tests\Legacy\IntegrationNew\BaseFixture\Server;

class Test extends TestCase
{
    use Server\Http;

    /**
     * @test
     */
    public function salaryArray()
    {
        $this->laravel
            ->get('/Praca?salary[]=1')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function salaryNonInteger()
    {
        $this->laravel
            ->get('/Praca?salary=foo')
            ->assertSuccessful();
    }
}
