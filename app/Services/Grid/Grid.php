<?php

namespace Coyote\Services\Grid;

use Collective\Html\HtmlBuilder;
use Coyote\Services\Grid\Source\SourceInterface;
use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

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
     * @var Order
     */
    protected $order;

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

        $this->makeDefaultOrder();
    }

    public function buildGrid()
    {
        //
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
     * @param array $options
     * @return $this
     */
    public function addColumn($name, array $options = [])
    {
        if ($name instanceof Column) {
            $column = $name;
        } else {
            $column = $this->makeColumn($name, $options);
        }

        $column->setGrid($this);
        $this->columns[$column->getName()] = $column;

        return $this;
    }

    /**
     * @param Order $order
     * @return $this
     */
    public function setDefaultOrder(Order $order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return Order
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
        $rows = $this->getRows();
        $paginator = $this->getPaginator($rows)->appends($this->request->except('page'));

        return view(self::DEFAULT_TEMPLATE, [
            'columns'       => $this->columns,
            'rows'          => $rows,
            'pagination'    => $paginator->render()
        ]);
    }

    /**
     * @param Rows $rows
     * @return LengthAwarePaginator
     */
    protected function getPaginator(Rows $rows)
    {
        return new LengthAwarePaginator($rows, $this->source->total(), $this->perPage, $this->resolveCurrentPage(), [
            'path' => $this->resolveCurrentPath(),
        ]);
    }

    /**
     * @return Rows
     */
    protected function getRows()
    {
        if ($this->request->has('column')) {
            $this->order = new Order(
                $this->request->get('column', $this->defaultOrder['column']),
                $this->request->get('direction', $this->defaultOrder['direction'])
            );

            $validator = $this->getValidatorInstance();

            if ($validator->fails()) {
                $this->makeDefaultOrder();
            }
        }
               

        $data = $this->execute();
        $rows = new Rows();

        foreach ($data as $item) {
            $row = new Row();

            foreach ($this->columns as $column) {
                $row->addCell(new Cell($column, $item));
            }

            $rows->addRow($row);
        }

        return $rows;
    }

    /**
     * @return mixed
     */
    protected function execute()
    {
        $this->source->setFiltersData($this->columns, $this->request);
        return $this->source->execute($this->perPage, $this->resolveCurrentPage(), $this->order);
    }

    /**
     * @return int
     */
    protected function resolveCurrentPage()
    {
        return Paginator::resolveCurrentPage();
    }

    /**
     * @return string
     */
    protected function resolveCurrentPath()
    {
        return Paginator::resolveCurrentPath();
    }

    /**
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function getValidatorInstance()
    {
        return $this->validator->make($this->request->all(), $this->getValidatorRules());
    }

    /**
     * @return array
     */
    protected function getValidatorRules()
    {
        $allowed = [];

        foreach ($this->columns as $column) {
            if ($column->isSortable()) {
                $allowed[] = $column->getName();
            }
        }

        return [
            'column' => 'sometimes|in:' . implode(',', $allowed),
            'direction' => 'sometimes|in:asc,desc'
        ];
    }

    protected function makeDefaultOrder()
    {
        $this->order = new Order($this->defaultOrder['column'], $this->defaultOrder['direction']);
    }

    /**
     * @param string $name
     * @param array $options
     * @return Column
     */
    protected function makeColumn($name, array $options = [])
    {
        $options = $this->setupColumnOptions($name, $options);

        return new Column($options);
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
