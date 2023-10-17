<?php
namespace Coyote\Services\Microblogs;

use Coyote\Microblog;
use Coyote\Models\Scopes\UserRelationsScope;
use Coyote\Repositories\Criteria\Microblog\LoadUserScope;
use Coyote\Repositories\Criteria\Microblog\OnlyMine;
use Coyote\Repositories\Criteria\Microblog\OrderById;
use Coyote\Repositories\Criteria\Microblog\OrderByScore;
use Coyote\Repositories\Criteria\Microblog\WithTag;
use Coyote\Repositories\Criteria\WithoutScope;
use Coyote\Repositories\Criteria\WithTrashed;
use Coyote\Repositories\Eloquent\MicroblogRepository;
use Coyote\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Eloquent;
use Illuminate\Pagination\LengthAwarePaginator;

class Builder
{
    private ?User $user;
    private bool $isSponsor = false;

    public function __construct(private MicroblogRepository $microblog, Guard $guard)
    {
        $this->user = $guard->user();
        if ($this->user) {
            $this->isSponsor = $this->user->is_sponsor;
        }
    }

    public function orderByScore(): self
    {
        $this->microblog->pushCriteria(new OrderByScore(!$this->isSponsor));
        return $this;
    }

    public function orderById(): self
    {
        $this->microblog->pushCriteria(new OrderById(!$this->isSponsor));
        return $this;
    }

    public function onlyUsers(User $user): self
    {
        $this->microblog->pushCriteria(new OnlyMine($user->id));
        $this->microblog->pushCriteria(new WithoutScope(UserRelationsScope::class));
        return $this;
    }

    public function withTag(string $tag): self
    {
        $this->microblog->pushCriteria(new WithTag($tag));
        return $this;
    }

    public function paginate(): LengthAwarePaginator
    {
        $count = (int)$this->microblog->applyCriteria(fn() => $this->microblog->count());
        $page = LengthAwarePaginator::resolveCurrentPage();
        $this->loadUserScope();
        $paginator = new LengthAwarePaginator(
            $this->microblog->forPage(10, $page),
            $count,
            10,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
        $this->microblog->resetCriteria();
        /** @var Eloquent\Collection $microblogs */
        $microblogs = $paginator->keyBy('id');
        $microblogs = $this->mergeComments($this->loadTopComments($microblogs), $microblogs);
        $paginator->setCollection($microblogs);
        return $paginator;
    }

    /**
     * @return \Coyote\Microblog[]
     */
    public function popular()
    {
        $this->loadUserScope();
        $result = $this->microblog->popular(5);
        $this->microblog->resetCriteria();
        $microblogs = $result->keyBy('id');

        return $this->mergeComments($this->loadTopComments($microblogs), $microblogs);
    }

    /**
     * @param int $id
     * @return Microblog
     */
    public function one(int $id): Microblog
    {
        $this->loadUserScope();

        if ($this->user && $this->user->can('microblog-delete')) {
            $this->microblog->pushCriteria(new WithTrashed());
        }

        $microblog = $this->microblog->findById($id);

        $comments = $this->loadComments($microblog);

        $microblog->setRelation('comments', $comments);
        $this->microblog->resetCriteria();

        return $microblog;
    }

    /**
     * @param \Illuminate\Support\Collection $microblogs
     * @return \Coyote\Microblog[]
     */
    private function loadTopComments($microblogs)
    {
        $this->loadUserScope();

        return $this->microblog->getTopComments($microblogs->keys()->toArray());
    }

    /**
     * @param Microblog $microblog
     * @return \Coyote\Microblog[]
     */
    private function loadComments(Microblog $microblog)
    {
        return $this->microblog->getComments($microblog->id);
    }

    /**
     * @param \Coyote\Microblog[] $comments
     * @param \Illuminate\Database\Eloquent\Collection $microblogs
     * @return \Coyote\Microblog[]
     */
    private function mergeComments($comments, $microblogs)
    {
        foreach ($comments->groupBy('parent_id') as $relations) {
            /** @var \Coyote\Microblog $microblog */
            $microblog = &$microblogs[$relations[0]->parent_id];
            $microblog->setRelation('comments', $relations);
        }

        return $microblogs;
    }

    private function loadUserScope(): void
    {
        if ($this->user) {
            $this->microblog->pushCriteria(new LoadUserScope($this->user));
        }
    }
}
