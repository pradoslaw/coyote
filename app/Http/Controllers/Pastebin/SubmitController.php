<?php

namespace Coyote\Http\Controllers\Pastebin;

use Coyote\Http\Controllers\Controller;
use Coyote\Http\Forms\PastebinForm;
use Coyote\Repositories\Contracts\PastebinRepositoryInterface as PastebinRepository;
use Coyote\Services\Stream\Objects\Pastebin as Stream_Pastebin;
use Coyote\Services\Stream\Activities\Create as Stream_Create;

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
     * @param PastebinForm $form
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(PastebinForm $form)
    {
        $pastebin = \DB::transaction(function () use ($form) {
            $pastebin = $this->pastebin->create(['user_id' => $this->userId] + $form->all());
            stream(Stream_Create::class, (new Stream_Pastebin())->map($pastebin));

            return $pastebin;
        });

        return redirect()->route('pastebin.show', [$pastebin->id])->with('success', 'Wpis został prawidłowo dodany.');
    }
}
