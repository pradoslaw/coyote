<?php
namespace Coyote\Http\Controllers\Microblog;

use Coyote\Http\Controllers\RenderParams;
use Coyote\Http\Factories\CacheFactory;
use Coyote\Http\Resources\MicroblogCollection;
use Coyote\Http\Resources\MicroblogResource;
use Coyote\Http\Resources\UserResource;
use Coyote\Microblog;
use Coyote\Repositories\Eloquent\MicroblogRepository;
use Coyote\Services\Microblogs;
use Coyote\Services\Parser\Extensions\Emoji;
use Coyote\Tag;
use Illuminate\View\View;

class HomeController extends BaseController
{
    use CacheFactory;

    public function __construct(
        private MicroblogRepository $microblog,
        private Microblogs\Builder  $builder)
    {
        parent::__construct();
        $this->breadcrumb->push('Mikroblogi', route('microblog.home'));
    }

    public function index(): View
    {
        return $this->list(null, 'Mikroblogi');
    }

    public function tag(string $tag): View
    {
        $this->breadcrumb->push("Wpisy z tagiem: $tag", route('microblog.tag', [$tag]));
        $this->builder->withTag($tag);
        return $this->list(new RenderParams($tag), "Wpisy z tagiem: $tag");
    }

    private function list(?RenderParams $renderParams, string $headingTitle): View
    {
        return $this->view('microblog.home', [
            'flags'             => $this->flags(),
            'count'             => $this->microblog->count(),
            'count_user'        => $this->microblog->countForUser($this->userId),
            'pagination'        => new MicroblogCollection($this->builder->orderById()->paginate()),
            'route'             => request()->route()->getName(),
            'popular_tags'      => $this->microblog->popularTags($this->userId),
            'recommended_users' => UserResource::collection($this->microblog->recommendedUsers($this->userId)),
            'tags'              => $this->tags(),
            'render_params'     => $renderParams,
            'emojis'            => Emoji::all(),
            'microblogNewUrl'   => $this->microblogNewUrl(),
            'headingTitle'      => $headingTitle,
        ]);
    }

    public function show(int $id): View
    {
        $microblog = $this->builder->one($id);
        abort_if(!is_null($microblog->parent_id), 404);
        $excerpt = $this->title($microblog);
        $this->breadcrumb->push($excerpt, route('microblog.view', [$microblog->id]));
        MicroblogResource::withoutWrapping();
        $resource = new MicroblogResource($microblog);
        $resource->preserverKeys();
        return $this->view('microblog.view')->with([
            'flags'             => $this->flags(),
            'microblog'         => $resource,
            'excerpt'           => $excerpt,
            'popular_tags'      => $this->microblog->popularTags($this->userId),
            'recommended_users' => UserResource::collection($this->microblog->recommendedUsers($this->userId)),
            'tags'              => $this->tags(),
            'emojis'            => Emoji::all(),
        ]);
    }

    private function title(Microblog $microblog): string
    {
        $excerpt = excerpt($microblog->html);
        if ($excerpt) {
            return $excerpt;
        }
        return 'Wpis użytkownika: ' . $microblog->user->name;
    }

    private function tags(): array
    {
        $tags = $this->getCacheFactory()->remember('microblog:tags', 30 * 60, fn() => $this->microblog->getTags());
        [$tech, $others] = $tags->partition(fn(Tag $tag) => $tag->category_id === Tag\Category::LANGUAGE);
        return [
            'tech'   => $tech,
            'others' => $others->splice(0, 10),
        ];
    }

    private function microblogNewUrl(): ?string
    {
        if ($this->userId) {
            return route('profile', [$this->userId]);
        }
        return null;
    }
}
