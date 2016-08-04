<?php

namespace Boduch\Grid;

use Boduch\Grid\Components\Component;
use Boduch\Grid\Components\RowAction;
use Boduch\Grid\Source\SourceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class Grid
{
    /**
     * @var string
     */
    protected $template = 'laravel-grid::grid';

    /**
     * @var GridHelper
     */
    protected $gridHelper;

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
    protected $emptyMessage = 'Brak danych do wyświetlenia.';

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
    protected $viewData = [];

    /**
     * @param GridHelper $gridHelper
     */
    public function __construct(GridHelper $gridHelper)
    {
        $this->gridHelper = $gridHelper;

        $this->makeDefaultOrder();
    }

    public function buildGrid()
    {
        //
    }

    /**
     * @return GridHelper
     */
    public function getGridHelper()
    {
        return $this->gridHelper;
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
    public function getEmptyMessage()
    {
        return $this->emptyMessage;
    }

    /**
     * @param string $emptyMessage
     * @return $this
     */
    public function setEmptyMessage($emptyMessage)
    {
        $this->emptyMessage = $emptyMessage;

        return $this;
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
     * @param Component $component
     * @return $this
     */
    public function addComponent(Component $component)
    {
        $component->setGrid($this);
        $this->viewData[$component->getName()] = $component->render();

        return $this;
    }

    /**
     * @param array $viewData
     * @return $this
     */
    public function setViewData($viewData)
    {
        $this->viewData = $viewData;

        return $this;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $rows = $this->getRows();
        $pagination = null;

        if ($this->enablePagination) {
            $pagination = $this->getPaginator($rows)->appends($this->gridHelper->getRequest()->except('page'));
        }

        return $this->gridHelper->getView()->make($this->template, [
            'columns'       => $this->columns,
            'rows'          => $rows,
            'pagination'    => $pagination,
            'grid'          => $this,
            'is_filterable' => $this->isFilterable()
        ], $this->viewData);
    }

    /**
     * Is table filterable?
     *
     * @return bool
     */
    public function isFilterable()
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

        if ($this->gridHelper->getRequest()->has('column') && !empty($this->defaultOrder)) {
            $this->order = new Order(
                $this->gridHelper->getRequest()->get('column', $this->defaultOrder['column']),
                $this->gridHelper->getRequest()->get('direction', $this->defaultOrder['direction'])
            );

            $validator = $this->gridHelper->getValidatorInstance($this->getValidatorRules());

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

            $row->addCell((new Action($actions, $mixed))->setRowActions($this->rowActions));

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
        $this->source->applyFilters($this->columns);

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
