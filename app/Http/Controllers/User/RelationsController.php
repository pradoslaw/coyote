<?php
namespace Coyote\Http\Controllers\User;

use Coyote\Http\Resources\UserResource;
use Illuminate\Contracts\Cache;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\View\View;

class RelationsController extends BaseController
{
    public function __construct(private Cache\Repository $cache)
    {
        parent::__construct();
    }

    public function showRelations(): View
    {
        $this->breadcrumb->push('Zablokowani oraz obserwowani', route('user.relations'));
        $users = $this->auth->relations()
            ->with(['relatedUser' => fn(BelongsTo $builder) => $builder->withTrashed()])
            ->get()
            ->pluck('relatedUser');

        return $this->view('user.relations', [
            'users' => UserResource::collection($users),
        ]);
    }

    public function block(int $relatedUserId): void
    {
        abort_if($relatedUserId === $this->userId, 422);

        $this->auth->relations()->updateOrInsert(['related_user_id' => $relatedUserId, 'user_id' => $this->userId], ['is_blocked' => true]);
        $this->clearCache();
    }

    public function unblock(int $relatedUserId): void
    {
        $this->auth->relations()->where('related_user_id', $relatedUserId)->delete();
        $this->clearCache();
    }

    public function follow(int $relatedUserId): void
    {
        abort_if($relatedUserId === $this->userId, 500);

        $this->auth->relations()->updateOrInsert(['related_user_id' => $relatedUserId, 'user_id' => $this->userId], ['is_blocked' => false]);
        $this->clearCache();
    }

    private function clearCache(): void
    {
        $this->cache->forget('followers:' . $this->userId);
    }
}
