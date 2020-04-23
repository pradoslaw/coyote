<?php

namespace Coyote\Console\Commands\Elasticsearch;

trait EsTrait
{
    /**
     * @return array
     */
    public function getSuitableModels()
    {
        $result = [];

        foreach (glob(app_path('Models/*.php')) as $filename) {
            $name = ucfirst(basename($filename, '.php'));
            $className = 'Coyote\\' . $name;
            $resource = "Coyote\\Http\\Resources\\Elasticsearch\\{$name}Resource";

            if (class_exists($resource)) {
                $result[] = $className;
            }
        }

        return $result;
    }

    protected function dispatch()
    {
        $model = ucfirst($this->option('model'));

        if (!$model) {
            $this->all();
        } else {
            $this->one($model);
        }
    }
}
