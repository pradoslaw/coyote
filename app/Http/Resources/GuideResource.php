<?php

namespace Coyote\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $text
 * @property string $excerpt
 */
class GuideResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $parser = resolve('parser.post');

        return array_merge(
            parent::toArray($request),
            [
                'html' => $parser->parse($this->text),
                'excerpt_html' => $parser->parse($this->excerpt)
            ]
        );
    }
}
