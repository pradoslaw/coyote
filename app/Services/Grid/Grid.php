<?php

namespace Coyote\Services\Grid;

use Coyote\Services\Grid\Columns\Column;
use Coyote\Services\Grid\Source\SourceInterface;
use Illuminate\Http\Request;

class Grid
{
    const DEFAULT_TEMPLATE = 'grid.grid';

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var SourceInterface
     */
    protected $source;

    /**
     * @var Column[]
     */
    protected $columns = [];

    /**
     * Grid constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param SourceInterface $source
     * @return $this
     */
    public function setSource(SourceInterface $source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @param array $options
     * @return $this
     */
    public function addColumn($name, $type = '', array $options = [])
    {
        if ($name instanceof Column) {
            $this->columns[] = $name;
        } else {
            $this->columns[] = $this->makeColumn($name, $type, $options);
        }

        return $this;
    }

    /**
     * @param string $column
     * @param string $order
     */
    public function setDefaultOrder($column, $order)
    {
        
    }

    /**
     * @return string
     */
    public function render()
    {
        return view(self::DEFAULT_TEMPLATE, [
            'columns' => $this->columns
        ]);
    }

    /**
     * @param string $name
     * @param string $type
     * @param array $options
     * @return Column
     */
    protected function makeColumn($name, $type, array $options = [])
    {
        $fieldType = $this->getFieldType($type);
        $options = $this->setupColumnOptions($name, $options);

        return new $fieldType($options);
    }

    /**
     * @param string $type
     * @return string
     */
    protected function getFieldType($type)
    {
        return __NAMESPACE__ . '\\Columns\\' . ucfirst(camel_case($type));
    }

    /**
     * @param string $name
     * @param array $options
     * @return array
     */
    protected function setupColumnOptions($name, array $options)
    {
        $default = ['name' => $name];
        return array_merge($default, $options);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}
