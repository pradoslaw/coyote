<?php namespace Coyote\Http\Controllers;

use Breadcrumb\Breadcrumb;
use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use DispatchesCommands, ValidatesRequests;

    protected $breadcrumb;

    public function __construct()
    {
        $this->breadcrumb = new Breadcrumb();
    }

    protected function view($view = null, $data = [])
    {
        if (count($this->breadcrumb)) {
            $data['breadcrumb'] = $this->breadcrumb->render();
        }

        return view($view, $data);
    }
}
