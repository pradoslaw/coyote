<?php

namespace Coyote\Http\Controllers\Guide;

use Coyote\Guide;
use Coyote\Http\Controllers\Controller;
use Coyote\Http\Resources\TagResource;
use Coyote\Repositories\Contracts\TagRepositoryInterface as TagRepository;

abstract class BaseController extends Controller
{
    public function __construct(protected TagRepository $tagRepository)
    {
        parent::__construct();

        TagResource::urlResolver(fn(string $name) => route('guide.tag', [urlencode($name)]));

        $this->breadcrumb->push('Rekrutacyjne Q&A', route('guide.home'));
    }

    /**
     * @inheritdoc
     */
    public function view($view = null, $data = [])
    {
        return parent::view($view, $data)->with([
          'popular_tags' => $this->tagRepository->popularTags(Guide::class)->groupBy('category')
        ]);
    }
}
