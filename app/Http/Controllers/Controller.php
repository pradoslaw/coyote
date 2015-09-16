<?php namespace Coyote\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Breadcrumb\Breadcrumb;

abstract class Controller extends BaseController {

	use DispatchesCommands, ValidatesRequests;

	protected $breadcrumb;

	function __construct()
	{
		$this->breadcrumb = new Breadcrumb();
	}

}
