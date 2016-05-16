<?php

namespace Coyote\Http\Controllers\Pastebin;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Forms\PastebinForm;
use Coyote\Repositories\Contracts\PastebinRepositoryInterface as PastebinRepository;

class SubmitController extends Controller
{
    /**
     * @var PastebinRepository
     */
    protected $pastebin;

    /**
     * @param PastebinRepository $pastebin
     */
    public function __construct(PastebinRepository $pastebin)
    {
        parent::__construct();
        
        $this->pastebin = $pastebin;
    }

    /**
     * Zapis tresci pastebin do bazy danych
     *
     * @param PastebinForm $pastebin
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(PastebinForm $pastebin)
    {
        $entry = $this->pastebin->create(['user_id' => $this->userId] + $pastebin->all());
        return redirect()->route('pastebin.show', [$entry->id]);
    }
}
