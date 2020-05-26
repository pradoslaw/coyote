<?php

namespace Coyote\Services\Microblogs;

use Coyote\Microblog;
use Coyote\Repositories\Contracts\MicroblogRepositoryInterface;
use Coyote\Repositories\Criteria\Microblog\LoadUserScope;
use Coyote\Repositories\Criteria\Microblog\OnlyMine;
use Coyote\Repositories\Criteria\Microblog\OrderById;
use Coyote\Repositories\Criteria\Microblog\OrderByScore;
use Coyote\Repositories\Criteria\Microblog\WithTag;
use Coyote\User;

class Builder
{
    /**
     * @var MicroblogRepositoryInterface
     */
    private $microblog;

    /**
     * @var User|null
     */
    private $user;

    /**
     * @param MicroblogRepositoryInterface $microblog
     */
    public function __construct(MicroblogRepositoryInterface $microblog)
    {
        $this->microblog = $microblog;
    }

    /**
     * @param User|null $user
     * @return $this
     */
    public function forUser(?User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return $this
     */
    public function orderByScore()
    {
        $this->microblog->pushCriteria(new OrderByScore());

        return $this;
    }

    /**
     * @return $this
     */
    public function orderById()
    {
        $this->microblog->pushCriteria(new OrderById());

        return $this;
    }

    /**
     * @return $this
     */
    public function onlyMine()
    {
        if ($this->user) {
            $this->microblog->pushCriteria(new OnlyMine($this->user->id));
        }

        return $this;
    }

    /**
     * @param string $tag
     * @return $this
     */
    public function withTag(string $tag)
    {
        $this->microblog->pushCriteria(new WithTag($tag));

        return $this;
    }

    /**
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate()
    {
        $this->loadUserScope();

        $paginator = $this->microblog->paginate(10);
        $this->microblog->resetCriteria();

        /** @var \Illuminate\Database\Eloquent\Collection $microblogs */
        $microblogs = $paginator->keyBy('id');

        $comments = $this->loadTopComments($microblogs);
        $microblogs = $this->mergeComments($comments, $microblogs);

        $paginator->setCollection($microblogs);

        return $paginator;
    }

    /**
     * @return \Coyote\Microblog[]
     */
    public function popular()
    {
        $this->loadUserScope();

        $result = $this->microblog->getPopular(5);

        $this->microblog->resetCriteria();

        /** @var \Illuminate\Database\Eloquent\Collection $microblogs */
        $microblogs = $result->keyBy('id');

        $comments = $this->loadTopComments($microblogs);

        return $this->mergeComments($comments, $microblogs);
    }

    /**
     * @param int $id
     * @return Microblog
     */
    public function one(int $id): Microblog
    {
        $this->loadUserScope();

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
            /** @var \Coyote\Microblog $microblog  */
            $microblog = &$microblogs[$relations[0]->parent_id];
            $microblog->setRelation('comments', $relations);
        }

        return $microblogs;
    }

    private function loadUserScope()
    {
        if ($this->user) {
            $this->microblog->pushCriteria(new LoadUserScope($this->user->id));
        }
    }
}
