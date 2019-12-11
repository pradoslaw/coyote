<?php

namespace Coyote\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $text
 * @property int $id
 * @property \Carbon\Carbon $created_at
 * @property \Coyote\User $author
 */
class PmResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $only = $this->resource->only(['id', 'read_at', 'folder', 'name']);

        return array_merge($only, [
            'url'                   => route('user.pm.show', [$this->id]),
            'created_at'            => format_date($this->created_at),
            'text'                  => excerpt($this->text, 50),
            'photo'                 => $this->photo ?: (string) $this->author->photo // todo: refaktoryzacja wiadomosci prywatnych
        ]);
    }
}
