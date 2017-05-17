<?php

namespace Coyote\Http\Controllers\Adm;

use Coyote\Http\Factories\CacheFactory;
use Coyote\Repositories\Contracts\WordRepositoryInterface as WordRepository;
use Illuminate\Http\Request;

class WordsController extends BaseController
{
    use CacheFactory;

    /**
     * @var WordRepository
     */
    protected $word;

    /**
     * @param WordRepository $word
     */
    public function __construct(WordRepository $word)
    {
        parent::__construct();

        $this->word = $word;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->push('Cenzura', route('adm.words'));

        return $this->view('adm.words')->with('words', array_reverse($this->word->all()->toArray()));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request)
    {
        $original = $this->word->pluck('replacement', 'word');
        $input = array_combine($request->input('word'), $request->input('replacement'));

        $this->transaction(function () use ($original, $input) {
            // update...
            foreach (array_diff_assoc($input, $original) as $key => $value) {
                $this->word->update(['replacement' => $value], $key, 'word');
            }

            // insert
            foreach (array_filter(array_diff_key($input, $original)) as $key => $value) {
                $this->word->create(['word' => $key, 'replacement' => $value]);
            }

            // delete
            foreach (array_keys(array_diff_key($original, $input)) as $key) {
                $this->word->where('word', $key)->delete();
            }
        });

        return back()->with('success', 'Zmiany zostały zapisane.');
    }
}
