<?php

namespace Coyote\Http\Controllers\Guide;

use Coyote\Guide;
use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\TagRepositoryInterface as TagRepository;

abstract class BaseController extends Controller
{
    public function __construct(protected TagRepository $tagRepository)
    {
        parent::__construct();

        $this->breadcrumb->push('Rekrutacyjne Q&A', route('guide.home'));
    }

    public function view($view = null, $data = [])
    {
        return parent::view($view, $data)->with([
            'popular_tags'  => $this->tagRepository->popularTags(Guide::class)->groupBy('category')
        ]);
    }
}
