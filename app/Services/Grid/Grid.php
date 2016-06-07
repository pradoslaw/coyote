<?php

namespace Coyote\Services\Grid;

use Collective\Html\HtmlBuilder;
use Coyote\Services\Grid\Columns\Column;
use Coyote\Services\Grid\Source\SourceInterface;
use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;

class Grid
{
    const DEFAULT_TEMPLATE = 'grid.grid';

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ValidationFactory
     */
    protected $validator;

    /**
     * @var HtmlBuilder
     */
    protected $htmlBuilder;

    /**
     * @var SourceInterface
     */
    protected $source;

    /**
     * @var Column[]
     */
    protected $columns = [];

    /**
     * @var int
     */
    protected $perPage = 15;

    /**
     * @var array
     */
    protected $defaultOrder = [
        'column' => 'id',
        'direction' => 'desc'
    ];

    /**
     * @var array
     */
    protected $order = [
        'column' => '',
        'direction' => ''
    ];

    /**
     * @param Request $request
     * @param ValidationFactory $validator
     * @param HtmlBuilder $htmlBuilder
     */
    public function __construct(Request $request, ValidationFactory $validator, HtmlBuilder $htmlBuilder)
    {
        $this->request = $request;
        $this->validator = $validator;
        $this->htmlBuilder = $htmlBuilder;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return HtmlBuilder
     */
    public function getHtmlBuilder()
    {
        return $this->htmlBuilder;
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
            $column = $name;
        } else {
            $column = $this->makeColumn($name, $type, $options);
        }

        $column->setGrid($this);
        $this->columns[$column->getName()] = $column;

        return $this;
    }

    /**
     * @param string $column
     * @param string $direction
     * @return $this
     */
    public function setDefaultOrder($column, $direction)
    {
        $this->defaultOrder = [
            'column'    => $column,
            'direction' => $direction
        ];

        return $this;
    }

    /**
     * @return array
     */
    public function getDefaultOrder()
    {
        return $this->defaultOrder;
    }

    /**
     * @return array
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param int $perPage
     * @return $this
     */
    public function setPerPage($perPage)
    {
        $this->perPage = $perPage;

        return $this;
    }

    /**
     * @return int
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * @return string
     */
    public function render()
    {
        $paginator = $this->getPaginator()->appends($this->request->except('page'));

        return view(self::DEFAULT_TEMPLATE, [
            'columns'       => $this->columns,
            'data'          => $paginator->items(),
            'pagination'    => $paginator->render()
        ]);
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    protected function getPaginator()
    {
        $this->order = [
            'column'    => $this->request->get('column', $this->defaultOrder['column']),
            'direction' => $this->request->get('direction', $this->defaultOrder['direction'])
        ];

        $validator = $this->getValidatorInstance();

        if ($validator->fails()) {
            $this->order = $this->defaultOrder;
        }

        $this->source->orderBy($this->order['column'], $this->order['direction']);

        return $this->source->paginate($this->perPage);
    }

    /**
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function getValidatorInstance()
    {
        $allowed = [];

        foreach ($this->columns as $column) {
            if ($column->isSortable()) {
                $allowed[] = $column->getName();
            }
        }

        return $this->validator->make($this->request->all(), [
            'column' => 'sometimes|in:' . implode(',', $allowed),
            'direction' => 'sometimes|in:asc,desc'
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
