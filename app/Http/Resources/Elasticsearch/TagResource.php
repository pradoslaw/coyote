<?php

namespace Coyote\Http\Resources\Elasticsearch;

use Coyote\Job;
use Coyote\Microblog;
use Coyote\Services\Media\File;
use Coyote\Topic;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property File $logo
 */
class TagResource extends JsonResource
{
    public function toArray($request)
    {
        $resources = $this->resource->resources;

        return array_merge(
            array_except(parent::toArray($request), ['created_at', 'updated_at', 'category_id', 'last_used_at']),
            [
                'logo' => (string)$this->logo->url(),

                'topics'     => $resources[Topic::class] ?? 0,
                'microblogs' => $resources[Microblog::class] ?? 0,
                'jobs'       => $resources[Job::class] ?? 0,
            ],
        );
    }
}
