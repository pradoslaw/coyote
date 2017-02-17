<?php

namespace Coyote\Services\Session;

use Carbon\Carbon;
use Illuminate\Database\Query\Expression;
use Illuminate\Http\Request;
use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Contracts\Auth\Guard;
use Jenssegers\Agent\Agent;

class Handler extends DatabaseSessionHandler
{
    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        $session = (object) $this->getQuery()->find($sessionId);

        if (isset($session->updated_at)) {
            if ($session->updated_at < Carbon::now()->subMinutes($this->minutes)) {
                $this->exists = true;

                return;
            }
        }

        if (isset($session->payload)) {
            $this->exists = true;

            return base64_decode($session->payload);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data)
    {
        $payload = $this->getDefaultPayload($data);

        if (!$this->exists) {
            $this->read($sessionId);
        }

        if ($this->exists) {
            $this->getQuery()->where('id', $sessionId)->update($payload);
        } else {
            $payload['id'] = $sessionId;

            $this->insert($payload);
        }

        $this->exists = true;
    }

    /**
     * Get the default payload for the session.
     *
     * @param  string  $data
     * @return array
     */
    protected function getDefaultPayload($data)
    {
        $payload = ['payload' => base64_encode($data), 'updated_at' => new Expression('NOW()')];

        if (!$container = $this->container) {
            return $payload;
        }

        if ($container->bound(Guard::class)) {
            $payload['user_id'] = $container->make(Guard::class)->id();
        }

        if ($container->bound('request')) {
            $request = $container->make('request');

            $payload['ip'] = $request->ip();
            $payload['url'] = str_limit($request->fullUrl(), 3999);
            $payload['browser'] = substr((string) $request->header('User-Agent'), 0, 500);
            $payload['robot'] = $this->robot($payload['browser']);

            $payload = $this->filterUrl($request, $payload);
        }

        return $payload;
    }

    /**
     * @param string $browser
     * @return null|string
     */
    private function robot($browser)
    {
        if ($this->exists) {
            return null;
        }

        $agent = new Agent();
        if (!$agent->isRobot($browser)) {
            return null;
        }

        return $agent->robot();
    }

    /**
     * @param array $payload
     * @throws \Exception
     */
    private function insert($payload)
    {
        try {
            $this->getQuery()->insert($payload);
        } catch (\PDOException $e) {
            // tutaj moze byc blad z zapisem sesji w przypadku zapytan ajax
            // @see https://github.com/laravel/framework/issues/9251
            // docelowo implementacja bedzie zastapiona na redis
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function gc($lifetime)
    {
        $this->getQuery()->where('updated_at', '<=', new Expression("NOW() - INTERVAL '$lifetime seconds'"))->delete();
    }

    /**
     * Filter url from data. We don't need to save ajax url.
     *
     * @param Request $request
     * @param array $data
     * @return array
     */
    private function filterUrl(Request $request, $data)
    {
        if ($request->ajax()) {
            unset($data['url']);
        }

        return $data;
    }
}
