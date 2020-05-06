<?php

namespace Coyote\Http\Composers;

use Coyote\Services\Forum\UserDefined;
use Coyote\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;

class InitialStateComposer
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var UserDefined
     */
    private $userDefined;

    /**
     * @param Request $request
     * @param UserDefined $userDefined
     */
    public function __construct(Request $request, UserDefined $userDefined)
    {
        $this->request = $request;
        $this->userDefined = $userDefined;
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
                'created_at'            => $user->created_at->toIso8601String(),
                'photo'                 => (string) $user->photo->url()
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
        $allowed = array_pluck($this->userDefined->getAllowedForums($user), 'id');

        $token = (new \Lcobucci\JWT\Builder())
            ->issuedAt(now()->timestamp)
            ->expiresAt(now()->addDays(7)->timestamp)
            ->issuedBy($user->id)
            ->withClaim('channel', "user:$user->id")
            ->withClaim('allowed', $allowed)
            ->getToken($signer, new Key(config('app.key')));

        return (string) $token;
    }
}
