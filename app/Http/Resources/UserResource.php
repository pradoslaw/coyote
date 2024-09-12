<?php
namespace Coyote\Http\Resources;

use Coyote\Domain\Initials;
use Coyote\Services\Media\File;
use Coyote\Services\Parser\Factories\SigFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property File $photo
 * @property string $sig
 * @property bool $allow_sig
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $parent = $this->resource->only([
            'id', 'name', 'is_online', 'bio', 'location',
            'allow_sig', 'allow_count', 'allow_smilies',
            'posts', 'location',
            'visited_at', 'created_at', 'group_name',
        ]);
        return \array_merge(
            \array_filter($parent, fn($value) => $value !== null),
            [
                'photo'      => (string)$this->photo->url() ?: null,
                'deleted_at' => $this->resource->deleted_at,
                'is_blocked' => $this->resource->is_blocked,
                'initials'   => (new Initials)->of($this->name),
            ],
            $this->isSignatureAllowed($request)
                ? ['sig' => $this->getParser()->parse($this->sig)]
                : [],
        );
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
