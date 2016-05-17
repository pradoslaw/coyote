<?php

namespace Coyote\Http\Controllers\Pastebin;

use Coyote\Http\Controllers\Controller;
use Coyote\Services\Stream\Objects\Pastebin as Stream_Pastebin;
use Coyote\Services\Stream\Activities\Delete as Stream_Delete;

class DeleteController extends Controller
{
    /**
     * @param \Coyote\Pastebin $pastebin
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index($pastebin)
    {
        \DB::transaction(function () use ($pastebin) {
            $pastebin->delete();
            stream(Stream_Delete::class, (new Stream_Pastebin())->map($pastebin));
        });

        return redirect()->route('pastebin.show')->with('success', 'Wpis został poprawnie usunięty.');
    }
}
