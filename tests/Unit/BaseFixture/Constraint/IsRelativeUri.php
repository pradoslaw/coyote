<?php
namespace Tests\Unit\BaseFixture\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Tests\Unit\BaseFixture\Server\Laravel;

class IsRelativeUri extends Constraint
{
    public function __construct(
        private string           $relativeUri,
        private Laravel\TestCase $laravel)
    {
    }

    protected function matches($other): bool
    {
        return $this->relativeUri === $this->relativeUri($other);
    }

    private function relativeUri(string $url): string
    {
        $basePath = $this->laravel->app->make('config')->get('app.url');
        if (\str_starts_with($url, $basePath)) {
            return \subStr($url, \strLen($basePath));
        }
        throw new \AssertionError("Failed to assert that uri $url starts with $basePath");
    }

    public function toString(): string
    {
        return "has relative uri '$this->relativeUri'";
    }
}
