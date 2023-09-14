<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\User\Menu\SettingsMenu;
use Coyote\Http\Resources\UserResource;
use Illuminate\Contracts\Cache;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\View\View;

class RelationsController extends BaseController
{
    use SettingsMenu;

    private Cache\Repository $cache;

    public function __construct(Cache\Repository $cache)
    {
        parent::__construct();
        $this->cache = $cache;
    }

    public function showRelations(): View
    {
        $users = $this->auth->relations()
          ->with(['relatedUser' => fn (BelongsTo $builder) => $builder->withTrashed()])
          ->get()
          ->pluck('relatedUser');

        return $this->view('user.relations', [
          'users' => UserResource::collection($users)
        ]);
    }

    public function block(int $relatedUserId): void
    {
        abort_if($relatedUserId === $this->userId, 500);

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
