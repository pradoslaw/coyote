<?php
namespace Coyote\Http\Controllers\Pastebin;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Forms\PastebinForm;
use Coyote\Pastebin;
use Coyote\Repositories\Eloquent\PastebinRepository;
use Illuminate\View\View;

class ShowController extends Controller
{
    public function __construct(private PastebinRepository $pastebin)
    {
        parent::__construct();
    }

    public function index(Pastebin $pastebin): View
    {
        $this->breadcrumb->push('Pastebin', route('pastebin.show'));
        $latest = $this->pastebin->latest()->limit(20)->get();
        return $this->view('pastebin.show', [
            'form'     => $this->createForm(PastebinForm::class, $pastebin),
            'latest'   => $latest,
            'pastebin' => $pastebin,
        ]);
    }
}
