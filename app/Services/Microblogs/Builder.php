<?php

namespace Coyote\Services\Microblogs;

use Coyote\Repositories\Contracts\MicroblogRepositoryInterface;
use Coyote\Repositories\Criteria\Microblog\LoadUserScope;
use Coyote\Repositories\Criteria\Microblog\OnlyMine;
use Coyote\Repositories\Criteria\Microblog\OrderById;
use Coyote\Repositories\Criteria\Microblog\OrderByScore;
use Coyote\Repositories\Criteria\Microblog\WithTag;
use Coyote\User;

class Builder
{
    private $microblog;
    private $user;

    public function __construct(MicroblogRepositoryInterface $microblog)
    {
        $this->microblog = $microblog;
    }

    public function forUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    public function orderByScore()
    {
        $this->microblog->pushCriteria(new OrderByScore());

        return $this;
    }

    public function orderById()
    {
        $this->microblog->pushCriteria(new OrderById());

        return $this;
    }

    public function onlyMine()
    {
        $this->microblog->pushCriteria(new OnlyMine($this->user->id));

        return $this;
    }

    public function withTag(string $tag)
    {
        $this->microblog->pushCriteria(new WithTag($tag));

        return $this;
    }

    public function paginate()
    {
        $this->microblog->pushCriteria(new LoadUserScope($this->user->id));

        $paginator = $this->microblog->paginate(10);
        $this->microblog->resetCriteria();

        /** @var \Illuminate\Database\Eloquent\Collection $microblogs */
        $microblogs =  $paginator->keyBy('id');

        $comments = $this->loadComments($microblogs);
        $microblogs = $this->mergeComments($comments, $microblogs);

        $paginator->setCollection($microblogs);

        return $paginator;
    }

    public function popular()
    {
        $this->microblog->pushCriteria(new LoadUserScope($this->user->id));

        $result = $this->microblog->getPopular(5);

        $this->microblog->resetCriteria();

        /** @var \Illuminate\Database\Eloquent\Collection $microblogs */
        $microblogs =  $result->keyBy('id');

        $comments = $this->loadComments($microblogs);

        return $this->mergeComments($comments, $microblogs);
    }

    private function loadComments($microblogs)
    {
        $this->microblog->pushCriteria(new LoadUserScope($this->user->id));

        return $this->microblog->getTopComments($microblogs->keys()->toArray());
    }

    private function mergeComments($comments, $microblogs)
    {
        foreach ($comments->groupBy('parent_id') as $relations) {
            /** @var \Coyote\Microblog $microblog  */
            $microblog = &$microblogs[$relations[0]->parent_id];
            $microblog->setRelation('comments', $relations);
        }

        return $microblogs;
    }
}
