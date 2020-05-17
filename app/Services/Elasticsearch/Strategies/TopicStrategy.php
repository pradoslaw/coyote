<?php


namespace Coyote\Services\Elasticsearch\Strategies;

use Coyote\Http\Resources\TopicCollection;
use Coyote\Http\Resources\TopicResource;
use Coyote\Repositories\Contracts\TopicRepositoryInterface as TopicRepository;
use Coyote\Services\Guest;
use Coyote\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class TopicStrategy extends Strategy
{
    /**
     * @var TopicRepository
     */
    private $repository;

    public function __construct(TopicRepository $repository)
    {
        $this->repository = $repository;
    }

    public function search(Request $request): JsonResponse
    {
        $guestId = $request->session()->get('guest_id');

        $hits = $this->api->search($request->input('q'), class_basename(Topic::class));
        $ids = array_pluck($hits->hits, 'id');

        $result = $this->repository->findByIds($ids, $request->user()->id ?? null, $guestId);

        $guest = new Guest($guestId);
        $paginator = new LengthAwarePaginator($result, $hits->total, 10);

        $collection = (new TopicCollection($paginator))
            ->setGuest($guest)
            ->setRepository($this->repository)
            ->additional(['took' => $hits->took]);

        return $collection->toResponse($request);
    }
}
