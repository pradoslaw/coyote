<?php

namespace Coyote\Session;

use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Support\Facades\DB;

class Handler extends DatabaseSessionHandler
{
    /**
     * {@inheritDoc}
     */
    public function write($sessionId, $data)
    {
        $userId  = auth()->check() ? auth()->user()->id : null;
        $url     = request()->fullUrl();
        $ip      = request()->ip();
        $browser = request()->browser();

        $data = [
            'payload'       => base64_encode($data),
            'updated_at'    => DB::raw('NOW()'),
            'user_id'       => $userId,
            'url'           => $url,
            'ip'            => $ip,
            'browser'       => $browser
        ];

        if ($this->exists) {
            $this->getQuery()->where('id', $sessionId)->update($data);
        } else {
            $this->getQuery()->insert(['id' => $sessionId] + $data);
        }

        $this->exists = true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc($lifetime)
    {
        $this->getQuery()->where('updated_at', '<=', DB::raw("NOW() - INTERVAL '$lifetime seconds'"))->delete();
    }
}