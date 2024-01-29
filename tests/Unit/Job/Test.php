<?php
namespace Tests\Unit\Job;

use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture\Server;

class Test extends TestCase
{
    use Server\Http;

    public function test()
    {
        $this->laravel
            ->get('/Praca?salary[]=1')
            ->assertSuccessful();
    }
}
