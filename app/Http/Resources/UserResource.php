<?php

namespace Coyote\Http\Resources;

use Coyote\Services\Parser\Factories\SigFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \Coyote\Services\Media\MediaInterface $photo
 * @property string $sig
 * @property bool $allow_sig
 */
class UserResource extends JsonResource
{
    private const OPTIONALS = ['allow_sig', 'allow_count', 'allow_smilies', 'posts', 'location', 'visited_at', 'created_at', 'group_name'];

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $result = array_merge(
            $this->resource->only(['id', 'name', 'deleted_at', 'is_blocked', 'is_online']),
            [
                'photo' => (string) $this->photo->url() ?: null,

                $this->mergeWhen($this->isSignatureAllowed($request), function () {
                    return ['sig' => $this->getParser()->parse($this->sig)];
                })
            ]
        );

        foreach (self::OPTIONALS as $value) {
            if (isset($this->resource[$value])) {
                $result[$value] = $this->resource->$value;
            }
        }

        return $result;
    }

    private function getParser(): SigFactory
    {
        static $instance = null;

        if ($instance === null) {
            $instance = app('parser.sig');
        }

        return $instance;
    }

    private function isSignatureAllowed(Request $request): bool
    {
        return $this->sig && $this->allow_sig && (!$request->user() || $request->user()->allow_sig);
    }
}
