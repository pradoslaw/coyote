<?php

namespace Coyote\Services\Session;

use Carbon\Carbon;
use Coyote\Repositories\Contracts\GuestRepositoryInterface as GuestRepository;
use Illuminate\Http\Request;

class Guest
{
    /**
     * @var GuestRepository
     */
    protected $guest;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @param GuestRepository $guest
     * @param Request $request
     */
    public function __construct(GuestRepository $guest, Request $request)
    {
        $this->guest = $guest;
        $this->request = $request;
    }

    /**
     * @return Carbon
     */
    public function guessVisit(): Carbon
    {
        static $createdAt;

        if (!empty($createdAt)) {
            return $createdAt;
        }

        $result = $this->guest->find($this->request->session()->get('guest_id'), ['created_at']);

        if ($result === null) {
            $createdAt = Carbon::createFromTimestamp($this->request->session()->get('created_at'));
        } else {
            $createdAt = $result->created_at;
        }

        return $createdAt;
    }
}
