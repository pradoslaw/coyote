<?php

namespace Coyote\Http\Resources;

use Coyote\Pm;
use Coyote\Services\Media\Factory;
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
        $only = $this->resource->only(['id', 'folder', 'name']);

        return array_merge($only, [
            'url'                   => route('user.pm.show', [$this->id]),
            'created_at'            => format_date($this->created_at),
            'text'                  => excerpt($this->parse($this->text), 50),
            'read_at'               => $this->folder == Pm::SENTBOX ? true : $this->read_at,
            'user'                  => new UserResource($this->author)
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
