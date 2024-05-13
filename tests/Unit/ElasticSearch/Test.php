<?php
namespace Tests\Unit\ElasticSearch;

use PHPUnit\Framework\TestCase;
use Tests\Unit\BaseFixture\Server\Laravel;

class Test extends TestCase
{
    use Laravel\Application;

    /**
     * @test
     */
    public function emptyQueryParamArrayTags()
    {
        $this->laravel->get('/Praca?tags[]')->assertSuccessful();
    }

    /**
     * @test
     */
    public function emptyQueryParamArrayCity()
    {
        $this->laravel->get('/Praca?city[]')->assertSuccessful();
    }
}
