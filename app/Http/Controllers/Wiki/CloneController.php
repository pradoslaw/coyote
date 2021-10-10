<?php

namespace Coyote\Http\Controllers\Wiki;

use Coyote\Events\WikiSaved;
use Coyote\Http\Forms\Wiki\CloneForm;
use Coyote\Services\Stream\Objects\Wiki as Stream_Wiki;
use Coyote\Services\Stream\Activities\Copy as Stream_Copy;

class CloneController extends BaseController
{
    /**
     * @param \Coyote\Wiki $wiki
     * @return \Illuminate\View\View
     */
    public function index($wiki)
    {
        return $this->view('wiki.clone', [
            'form' => $this->createForm(CloneForm::class, $wiki, [
                'url' => route('wiki.clone', [$wiki->id])
            ])
        ]);
    }

    /**
     * @param \Coyote\Wiki $wiki
     * @param CloneForm $form
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save($wiki, CloneForm $form)
    {
        $path = $this->transaction(function () use ($wiki, $form) {
            // clone page to new location
            $path = $this->wiki->clone($wiki->wiki_id, $form->get('parent_id')->getValue());
            $wiki->forceFill($path->toArray());

            $wiki->id = $wiki->path_id;

            stream(Stream_Copy::class, (new Stream_Wiki())->map($wiki));
            event(new WikiSaved($wiki));

            return $path->path;
        });

        return redirect()->to($path)->with('success', 'Strona poprawnie skopiowana.');
    }
}
