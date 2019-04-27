<?php

namespace Coyote\Exceptions;

use Coyote\Firewall;

class ForbiddenException extends \Exception
{
    /**
     * @var Firewall
     */
    public $firewall;

    /**
     * @param Firewall $firewall
     */
    public function __construct(Firewall $firewall)
    {
        parent::__construct('You are banned.', 403);

        $this->firewall = $firewall;
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        return response()->view('errors.forbidden', $this->firewall->toArray(), 401);
    }
}
