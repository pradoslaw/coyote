<?php

namespace Coyote\Http\Resources;

use Coyote\Pm;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $text
 * @property int $id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $read_at
 * @property \Coyote\User $author
 * @property int $folder
 */
class PmResource extends JsonResource
{
    /**
     * @var \Coyote\Services\Parser\Factories\PmFactory
     */
    private static $parser = null;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $only = $this->resource->only(['id', 'text_id', 'folder']);

        $text = $this->parse($this->text instanceof Pm\Text ? $this->text->text : $this->text);

        return array_merge($only, [
            'url'                   => route('user.pm.show', [$this->id]),
            'created_at'            => carbon($this->created_at)->toIso8601String(),
            'excerpt'               => excerpt($text, 50),
            'text'                  => $text,
            'read_at'               => $this->read_at ? carbon($this->read_at)->toIso8601String() : null,
            'user'                  => new UserResource($this->user)
        ]);
    }

    /**
     * @param string $text
     * @return string
     */
    private function parse(string $text): string
    {
        if (self::$parser === null) {
            self::$parser = app('parser.pm');
        }

        return self::$parser->parse($text);
    }
}
