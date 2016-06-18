<?php

namespace Coyote\Http\Controllers\Wiki;

use Coyote\Events\WikiWasSaved;
use Coyote\Http\Forms\Wiki\CloneForm;
use Coyote\Services\Stream\Objects\Wiki as Stream_Wiki;
use Coyote\Services\Stream\Activities\Copy as Stream_Copy;

class CloneController extends BaseController
{
    public function index($wiki)
    {
        return $this->view('wiki.clone', [
            'form' => $this->createForm(CloneForm::class, $wiki, [
                'url' => route('wiki.clone', $wiki->id)
            ])
        ]);
    }

    public function save($wiki, CloneForm $form)
    {
        $path = $this->transaction(function () use ($wiki, $form) {
            $path = $this->wiki->clone($wiki->id, $form->get('path_id')->getValue());
            $wiki->forceFill($path->toArray());

            stream(Stream_Copy::class, (new Stream_Wiki())->map($wiki));
        });

        return redirect()->to($path->path)->with('success', 'Strona poprawnie skopiowana.');
    }
}
