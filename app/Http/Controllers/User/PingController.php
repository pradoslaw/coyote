<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Coyote\Repositories\Contracts\SessionRepositoryInterface as SessionRepository;
use Illuminate\Http\Request;

class PingController extends Controller
{
    /**
     * @var SessionRepository
     */
    private $session;

    /**
     * @param SessionRepository $session
     */
    public function __construct(SessionRepository $session)
    {
        parent::__construct();

        $this->session = $session;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $this->session->extend($request->session()->getId());

        return response(csrf_token());
    }
}
