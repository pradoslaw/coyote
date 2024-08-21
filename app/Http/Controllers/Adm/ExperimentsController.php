<?php
namespace Coyote\Http\Controllers\Adm;

use Illuminate\View\View;

class ExperimentsController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->breadcrumb->push('Eksperymenty', '');
    }

    public function index(): View
    {
        return $this->view('adm.experiments.home');
    }
}
