<?php

namespace Boduch\Grid;

use Collective\Html\HtmlBuilder;
use Collective\Html\FormBuilder;
use Boduch\Grid\RowActions\RowAction;
use Boduch\Grid\Source\SourceInterface;
use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class Grid
{
    const DEFAULT_TEMPLATE = 'laravel-grid::grid';

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
     * @var FormBuilder
     */
    protected $formBuilder;

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
     * @var callable
     */
    protected $eachCallback;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param Request $request
     * @param ValidationFactory $validator
     * @param HtmlBuilder $htmlBuilder
     * @param FormBuilder $formBuilder
     */
    public function __construct(
        Request $request,
        ValidationFactory $validator,
        HtmlBuilder $htmlBuilder,
        FormBuilder $formBuilder
    ) {
        $this->request = $request;
        $this->validator = $validator;
        $this->htmlBuilder = $htmlBuilder;
        $this->formBuilder = $formBuilder;

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
     * @return FormBuilder
     */
    public function getFormBuilder()
    {
        return $this->formBuilder;
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
        $this->setPerPage(null);

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
     * @param callable $callback
     */
    public function each(callable $callback)
    {
        $this->eachCallback = $callback;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
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
        ], $this->data);
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
     * @return Rows
     */
    public function getRows()
    {
        if (empty($this->source)) {
            throw new \InvalidArgumentException('You MUST set the data grid source by calling setSource() method.');
        }

        if (!empty($this->rows)) {
            return $this->rows;
        }

        if ($this->request->has('column') && !empty($this->defaultOrder)) {
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
        $this->rows = new Rows();

        // special column for action buttons
        $actions = new Column(['name' => '__actions__']);
        $actions->setGrid($this);

        foreach ($data as $mixed) {
            $row = new Row($mixed);
            $row->setGrid($this);

            foreach ($this->columns as $column) {
                $row->addCell(new Cell($column, $mixed));
            }

            $row->addCell(new Action($actions, $this->rowActions, $mixed));

            if ($this->eachCallback) {
                $this->eachCallback->call($this, $row);
            }
            $this->rows->addRow($row);
        }

        $this->columns[] = $actions;

        return $this->rows;
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
     * @return mixed
     */
    protected function execute()
    {
        $this->source->applyFilters($this->columns, $this->request);

        if ($this->enablePagination) {
            $this->total = $this->source->total();
        }

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
        $this->order = $this->defaultOrder
            ? new Order($this->defaultOrder['column'], $this->defaultOrder['direction'])
            : new Order();
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
        return (string) $this->render();
    }
}
