<?php

namespace Coyote\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TopicCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = TopicResource::class;

    /**
     * @var string|null
     */
    private $guestId;

    /**
     * @param string|null $guestId
     * @return $this
     */
    public function setGuestId(?string $guestId)
    {
        $this->guestId = $guestId;

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
        return $this
            ->collection
            ->map(function (TopicResource $resource) use ($request) {
                return $resource->setGuestId($this->guestId)->toArray($request);
            })
            ->all();
    }
}
