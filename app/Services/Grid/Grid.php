<?php

namespace Coyote\Services\Grid;

use Boduch\Grid\Decorators\DateTimeLocalized;
use Boduch\Grid\Grid as BaseGrid;

class Grid extends BaseGrid
{
    /**
     * @var string
     */
    protected $template = 'grid.grid';

    /**
     * @return DateTimeLocalized
     */
    protected function getDateTimeDecorator()
    {
        return new DateTimeLocalized(auth()->check() ? auth()->user()->date_format : '%Y-%m-%d %H:%M');
    }
}
