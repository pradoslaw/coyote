<?php

namespace Tests\Feature;

use Coyote\Services\Elasticsearch\QueryParser;
use Tests\TestCase;

class QueryParserTest extends TestCase
{
    /**
     * @var array
     */
    private $keywords = ['ip', 'user', 'browser'];

    public function testParseIpQuery()
    {
        $parser = new QueryParser('ip:127.0.0.1', $this->keywords);
        $this->assertArrayHasKey('ip', $filters = $parser->getFilters());
        $this->assertEquals('127.0.0.1', $filters['ip']);
    }

    public function testParseUserQuery()
    {
        $parser = new QueryParser('user:admin', $this->keywords);
        $this->assertArrayHasKey('user', $filters = $parser->getFilters());
        $this->assertEquals('admin', $filters['user']);

        $parser = new QueryParser('user:"admin adminski"', $this->keywords);
        $this->assertArrayHasKey('user', $filters = $parser->getFilters());
        $this->assertEquals('admin adminski', $filters['user']);

        $parser = new QueryParser('user:"test"test', $this->keywords);
        $this->assertArrayHasKey('user', $filters = $parser->getFilters());
        $this->assertEquals('test', $filters['user']);
        $this->assertEquals('test', $parser->getFilteredQuery());
    }

    public function testParseRegularQuery()
    {
        $parser = new QueryParser('foo', $this->keywords);
        $this->assertEquals('foo', $parser->getFilteredQuery());
    }
}
