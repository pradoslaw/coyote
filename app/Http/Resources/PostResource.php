<?php

namespace Coyote\Http\Resources;

use Carbon\Carbon;
use Coyote\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property Carbon $created_at
 * @property string $user_name
 * @property User $user
 */
class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'created_at'    => $this->created_at->toIso8601String(),
            'user_name'     => $this->user_name,
            'user'          => UserResource::make($this->user),
        ];
    }
}
