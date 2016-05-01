<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\PollRepositoryInterface;

class PollRepository extends Repository implements PollRepositoryInterface
{
    /**
     * @return \Coyote\Poll
     */
    public function model()
    {
        return 'Coyote\Poll';
    }

    /**
     * @param int $id
     * @param mixed $data
     * @return \Coyote\Poll
     */
    public function updateOrCreate($id, $data)
    {
        /** @var \Coyote\Poll $poll */
        $poll = $this->model->findOrNew($id);
        $poll->fill($data)->save();

        $current = $poll->items()->lists('text');
        $next = $this->collect($data['items']);

        // to remove...
        $current->diff($next)->each(function ($value) use ($poll) {
            $poll->items()->where('text', $value)->delete();
        });

        $next->diff($current)->each(function ($value) use ($poll) {
            $poll->items()->create(['text' => $value]);
        });

        return $poll;
    }

    /**
     * @param string $items
     * @return mixed
     */
    private function collect(string $items)
    {
        return collect(explode("\n", $items))->map(function ($item) {
            return trim($item);
        });
    }
}
