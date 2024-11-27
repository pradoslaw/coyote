<?php
namespace Tests\Integration\Job;

use PHPUnit\Framework\TestCase;
use Tests\Integration\BaseFixture\Server;

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
