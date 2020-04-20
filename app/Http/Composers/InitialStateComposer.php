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

class InitialStateComposer
{
    private $forum;
    private $request;

    public function __construct(ForumRepository $forum, Request $request)
    {
        $this->forum = $forum;
        $this->request = $request;
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

    private function registerWebSocket(): array
    {
        if (config('services.ws.host') && $this->request->user()) {
            return ['ws' => config('services.ws.host') . (config('services.ws.port') ? ':' . config('services.ws.port') : '')];
        }

        return [];
    }

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
                'pm_unread'             => $user->pm_unread
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

        $token = (new \Lcobucci\JWT\Builder())
            ->issuedAt(now()->timestamp)
            ->expiresAt(now()->addDays(7)->timestamp)
            ->issuedBy($user->id)
            ->withClaim('channel', "user:$user->id")
            ->withClaim('prohibited', $this->getProhibitedForums($user))
            ->getToken($signer, new Key(config('app.key')));

        return (string) $token;
    }

    /**
     * @param User $user
     * @return array
     */
    private function getProhibitedForums(User $user): array
    {
        $this->forum->pushCriteria(new OnlyThoseWithAccess($user));

        return array_values($this->forum->findHiddenIds($user->id));
    }
}
