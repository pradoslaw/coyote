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
            $name = ucfirst($filename);
            $className = 'Coyote\\' . substr(pathinfo($filename, PATHINFO_BASENAME), 0, -4);
            $resource = "Coyote\\Http\\Resources\\Elasticsearch\\{$name}Resource";

            if (class_exists($resource, false)) {
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
