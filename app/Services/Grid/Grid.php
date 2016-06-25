<?php

namespace Coyote\Services\Grid;

use Collective\Html\HtmlBuilder;
use Coyote\Services\Grid\RowActions\RowAction;
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
     * @var string
     */
    protected $noDataMessage = 'Brak danych do wyświetlenia.';

    /**
     * @var Rows
     */
    protected $rows;

    /**
     * Total number of records.
     *
     * @var int
     */
    protected $total = 0;

    /**
     * @var RowAction[]
     */
    protected $rowActions = [];

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
     * @var bool
     */
    protected $enablePagination = true;

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
     * @return Column[]
     */
    public function getColumns()
    {
        return $this->columns;
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
    public function getNoDataMessage()
    {
        return $this->noDataMessage;
    }

    /**
     * @param string $noDataMessage
     */
    public function setNoDataMessage($noDataMessage)
    {
        $this->noDataMessage = $noDataMessage;
    }

    /**
     * @param RowAction $rowAction
     * @return $this
     */
    public function addRowAction(RowAction $rowAction)
    {
        $rowAction->setGrid($this);
        $this->rowActions[] = $rowAction;

        return $this;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setEnablePagination($flag)
    {
        $this->enablePagination = (bool) $flag;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPaginationEnabled()
    {
        return $this->enablePagination;
    }

    /**
     * @return string
     */
    public function render()
    {
        $rows = $this->getRows();
        $paginator = null;

        if ($this->enablePagination) {
            $paginator = $this->getPaginator($rows)->appends($this->request->except('page'))->render();
        }

        return view(self::DEFAULT_TEMPLATE, [
            'columns'       => $this->columns,
            'rows'          => $rows,
            'pagination'    => $paginator,
            'grid'          => $this,
            'has_filters'   => $this->hasFilters()
        ]);
    }

    /**
     * @return bool
     */
    public function hasFilters()
    {
        $hasFilters = false;

        foreach ($this->columns as $column) {
            if ($column->isFilterable()) {
                $hasFilters = true;
                break;
            }
        }

        return $hasFilters;
    }

    /**
     * @param Rows $rows
     * @return LengthAwarePaginator
     */
    protected function getPaginator(Rows $rows)
    {
        return new LengthAwarePaginator($rows, $this->total, $this->perPage, $this->resolveCurrentPage(), [
            'path' => $this->resolveCurrentPath(),
        ]);
    }

    /**
     * @return Rows
     */
    protected function getRows()
    {
        if (!empty($this->rows)) {
            return $this->rows;
        }

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

        if ($this->enablePagination) {
            $this->total = $this->source->total();
        }

        $data = $this->execute();
        $this->rows = new Rows();

        // special column for action buttons
        $actions = new Column(['name' => '__actions__']);
        $actions->setGrid($this);

        foreach ($data as $item) {
            $row = new Row();

            foreach ($this->columns as $column) {
                $row->addCell(new Cell($column, $item));
            }

            $row->addCell(new Action($actions, $this->rowActions, $item));
            $this->rows->addRow($row);
        }

        $this->columns[] = $actions;

        return $this->rows;
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
