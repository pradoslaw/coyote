<?php

namespace Coyote\Http\Resources;

use Coyote\Repositories\Contracts\TopicRepositoryInterface as TopicRepository;
use Coyote\Services\Forum\Tracker;
use Coyote\Services\Guest;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;

class TopicCollection extends ResourceCollection
{
    /**
     * @var TopicRepository
     */
    protected $repository;

    /**
     * @var Guest
     */
    protected $guest;

    /**
     * @param $collection
     * @return TopicCollection
     */
    public static function factory($collection)
    {
        return (new self($collection))->setGuest(app(Guest::class));
    }

    /**
     * @param TopicRepository $repository
     * @return $this
     */
    public function setRepository(TopicRepository $repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * @param Guest $guest
     * @return $this
     */
    public function setGuest(Guest $guest)
    {
        $this->guest = $guest;

        return $this;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
//        dd($this->collection);
//        dd($this->resource instanceof AbstractPaginator);
        return $this
            ->resource
            ->setCollection(
                $this
                    ->collection
                    ->map(function ($model) use ($request) {
                        $resource = (new Tracker($model, $this->guest))->setRepository($this->repository);

                        return (new TopicResource($resource))->toArray($request);
                    })
            )
            ->toArray();
    }
}
