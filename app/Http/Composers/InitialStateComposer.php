<?php

namespace Coyote\Http\Composers;

use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Repositories\Criteria\Forum\AccordingToUserOrder;
use Coyote\Repositories\Criteria\Forum\OnlyThoseWithAccess;
use Coyote\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use Illuminate\Contracts\Cache\Repository as Cache;

class InitialStateComposer
{
    /**
     * @var ForumRepository
     */
    private $forum;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @param ForumRepository $forum
     * @param Request $request
     * @param Cache $cache
     */
    public function __construct(ForumRepository $forum, Request $request, Cache $cache)
    {
        $this->forum = $forum;
        $this->request = $request;
        $this->cache = $cache;
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $state = array_merge(
            $this->request->attributes->all(),
            [
                'public'        => route('home'),
                'user' => [
                    'notifications_unread' => 0,
                    'pm_unread'     => 0
                ]
            ],
            $this->registerWebSocket(),
            $this->registerUserModel()
        );

        $view->with('__INITIAL_STATE', json_encode($state));
    }

    /**
     * @return array|string[]
     */
    private function registerWebSocket(): array
    {
        if (config('services.ws.host') && $this->request->user()) {
            return ['ws' => config('services.ws.host') . (config('services.ws.port') ? ':' . config('services.ws.port') : '')];
        }

        return [];
    }

    /**
     * @return array|array[]
     */
    private function registerUserModel(): array
    {
        if (empty($this->request->user())) {
            return [];
        }

        $user = $this->request->user();

        return [
            'user' => [
                'id'                    => $user->id,
                'date_format'           => $this->mapFormat($user->date_format),
                'token'                 => $this->getJWtToken($user),
                'notifications_unread'  => $user->notifications_unread,
                'pm_unread'             => $user->pm_unread,
                'created_at'            => $user->created_at->toIso8601String()
            ]
        ];
    }


    /**
     * @param string $format
     * @return string
     */
    private function mapFormat(string $format): string
    {
        $values = [
            'dd-MM-yyyy HH:mm',
            'yyyy-MM-dd HH:mm',
            'MM/dd/yy HH:mm',
            'dd-MM-yy HH:mm',
            'dd MMM yy HH:mm',
            'dd MMMM yyyy HH:mm'
        ];

        return array_combine(array_keys(User::dateFormatList()), $values)[$format];
    }

    /**
     * @param User $user
     * @return string
     */
    private function getJWtToken(User $user): string
    {
        $signer = new Sha256();
        $allowed = array_pluck($this->getAllowedForums($user), 'id');

        $token = (new \Lcobucci\JWT\Builder())
            ->issuedAt(now()->timestamp)
            ->expiresAt(now()->addDays(7)->timestamp)
            ->issuedBy($user->id)
            ->withClaim('channel', "user:$user->id")
            ->withClaim('allowed', $allowed)
            ->getToken($signer, new Key(config('app.key')));

        return (string) $token;
    }

    /**
     * @param User $user
     * @return array
     */
    private function getAllowedForums(User $user): array
    {
        return $this->cache->tags('forum-order')->remember('forum-order:' . $user->id, now()->addMonth(1), function () use ($user) {
            // since repository is singleton, we have to reset previously set criteria to avoid duplicated them.
            $this->forum->resetCriteria();
            // make sure we don't skip criteria
            $this->forum->skipCriteria(false);

            $this->forum->pushCriteria(new OnlyThoseWithAccess($user));
            $this->forum->pushCriteria(new AccordingToUserOrder($user->id, true));

            return $this->forum->list()->toArray();
        });
    }
}
