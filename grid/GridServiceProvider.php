<?php
namespace Boduch\Grid;

use Illuminate\Support\ServiceProvider;

class GridServiceProvider extends ServiceProvider
{
    protected bool $defer = true;

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'laravel-grid');
    }

    public function provides(): array
    {
        return [GridBuilder::class];
    }
}
