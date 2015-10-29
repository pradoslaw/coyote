<?php namespace Coyote\Http\Controllers;

use Coyote;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $breadcrumb;

    public function __construct()
    {
        $this->breadcrumb = new Coyote\Breadcrumb();
    }

    protected function view($view = null, $data = [])
    {
        if (count($this->breadcrumb)) {
            $data['breadcrumb'] = $this->breadcrumb->render();
        }

        return view($view, $data);
    }
}
