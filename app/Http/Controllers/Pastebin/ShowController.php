<?php

namespace Coyote\Http\Controllers\Pastebin;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Forms\PastebinForm;
use Coyote\Repositories\Contracts\PastebinRepositoryInterface as PatebinRepository;

class ShowController extends Controller
{
    /**
     * @var PatebinRepository
     */
    protected $pastebin;

    /**
     * @param PatebinRepository $pastebin
     */
    public function __construct(PatebinRepository $pastebin)
    {
        parent::__construct();

        $this->pastebin = $pastebin;
    }

    /**
     * @param \Coyote\Pastebin $pastebin
     * @return \Illuminate\View\View
     */
    public function index($pastebin)
    {
        $this->breadcrumb->push('Pastebin', route('pastebin.show'));

        $latest = $this->pastebin->latest()->limit(20)->get();

        return $this->view('pastebin.show', [
            'form' => $this->createForm(PastebinForm::class, $pastebin, [
                'url' => route('pastebin.submit')
            ]),
            'latest' => $latest,
            'pastebin' => $pastebin
        ]);
    }
}
