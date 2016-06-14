<?php

namespace Coyote\Console\Commands\Elasticsearch;

trait EsTrait
{
    public function getSuitableModels()
    {
        $result = [];
        
        foreach (glob(app_path('Models/*.php')) as $filename) {
            $className = 'Coyote\\' . substr(pathinfo($filename, PATHINFO_BASENAME), 0, -4);
            $reflection = new \ReflectionClass($className);

            $traits = $reflection->getTraits();
            if (isset($traits['Coyote\Searchable'])) {
                $result[] = $className;
            }
        }
        
        return $result;
    }
}
