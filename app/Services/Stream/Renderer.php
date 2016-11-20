<?php

namespace Coyote\Services\Stream;

use Illuminate\Support\Collection;

class Renderer
{
    /**
     * @var Collection|array
     */
    protected $collection;

    /**
     * @param Collection|array $collection
     */
    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    /**
     * @return array
     */
    public function render()
    {
        $result = [];

        foreach ($this->collection as $index => $row) {
            if (empty($row['object.objectType'])) {
                $row['object.objectType'] = 'unknown';
            }

            $class = __NAMESPACE__ . '\\Render\\' . ucfirst(camel_case($row['object.objectType']));
            $decorator = new $class($row);

            /** @var \Coyote\Services\Stream\Render\Render $decorator */
            $result[$index] = $decorator->render();
        }

        return $result;
    }
}
