<?php

namespace Coyote\Http\Resources;

use Carbon\Carbon;
use Coyote\Forum;
use Coyote\Services\Forum\Tracker;
use Coyote\Services\Parser\Factories\SigFactory;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property int $user_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property string $user_name
 * @property string $text
 * @property string $html
 * @property string $delete_reason
 * @property User $user
 * @property User|null $editor
 * @property User|null $deleter
 * @property int $score
 * @property int $edit_count
 * @property Topic $topic
 * @property Forum $forum
 * @property string $ip
 * @property string $host
 * @property string $browser
 */
class PostResource extends JsonResource
{
    /**
     * @var Tracker
     */
    protected $tracker;

    /**
     * @var SigFactory
     */
    protected $sigParser;

    /**
     * @param Tracker $tracker
     * @return $this
     */
    public function setTracker(Tracker $tracker)
    {
        $this->tracker = $tracker;

        return $this;
    }

    public function setSigParser(SigFactory $sigFactory)
    {
        $this->sigParser = $sigFactory;

        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $only = $this->resource->only(['id', 'user_name', 'score', 'text', 'edit_count', 'is_voted', 'is_accepted', 'is_subscribed', 'user_id', 'deleter_name', 'delete_reason']);
        $html = $this->text !== null ? $this->html : null;

        if ($this->isSignatureAllowed($request)) {
            $this->user->sig = $this->sigParser->parse($this->user->sig);
        }

        $auth = $request->user();

        return array_merge($only, [
            'created_at'    => $this->created_at->toIso8601String(),
            'updated_at'    => $this->updated_at ? $this->updated_at->toIso8601String() : null,
            'deleted_at'    => $this->deleted_at ? Carbon::parse($this->deleted_at)->toIso8601String() : null,
            'user'          => UserResource::make($this->user),
            'html'          => $html,
            'url'           => UrlBuilder::post($this->resource, true),
            'is_read'       => $this->tracker->getMarkTime() > $this->created_at,
            'is_locked'     => $this->topic->is_locked || $this->forum->is_locked,

            $this->mergeWhen($auth->can('update', $this->resource), function () {
               return ['ip' => $this->ip . ' ' . ($this->host ? "($this->host) $this->browser" : '')];
            }),

            $this->mergeWhen($this->editor !== null, function () {
                return ['editor' => UserResource::make($this->editor)];
            }),

            $this->mergeWhen($auth, function () use ($auth) {
                return ['permissions' => [
                    'write'             => $auth->can('write', $this->topic) && $auth->can('write', $this->forum),
                    'delete'            => $auth->can('delete', $this->topic) || $auth->can('delete', $this->forum),
                    'update'            => $auth->can('update', $this->resource),
                    'merge'             => $auth->can('merge', $this->forum),
                    'sticky'            => $auth->can('sticky', $this->forum),
                    'adm_access'        => $auth->can('adm-access')
                ]];
            }),

            'comments'      => PostCommentResource::collection($this->resource->comments)->keyBy('id'),
            'attachments'   => PostAttachmentResource::collection($this->resource->attachments)
        ]);
    }

    private function isSignatureAllowed(Request $request)
    {
        return !$request->user() || ($request->user() && $request->user()->allow_sig) && ($this->user_id && $this->user->sig);
    }
}
