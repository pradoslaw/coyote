<?php

namespace Boduch\Grid\Console;

use Illuminate\Console\GeneratorCommand;

class GridMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:grid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Laravel Grid class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Grid';

    /**
     * Determine if the class already exists.
     *
     * @param  string  $rawName
     * @return bool
     */
    protected function alreadyExists($rawName)
    {
        return class_exists($rawName);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/grid.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Http\Grids';
    }
}
