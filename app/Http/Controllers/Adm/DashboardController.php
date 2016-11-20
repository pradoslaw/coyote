<?php

namespace Coyote\Http\Controllers\Adm;

class DashboardController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        return $this->view('adm.dashboard', ['checklist' => [
            'Katalog <tt>storage</tt> z prawami do zapisu' => $this->isStorageWriteable(),
            'Katalog <tt>uploads</tt> z prawami do zapisu' => $this->isPublicWriteable(),
            'Redis włączony' => $this->isRedisEnabled(),
            'Redis aktywny' => $this->isRedisWorking()
        ]]);
    }

    /**
     * @return bool
     */
    private function isStorageWriteable()
    {
        return is_writeable(storage_path());
    }

    /**
     * @return bool
     */
    private function isPublicWriteable()
    {
        return is_writeable(public_path());
    }

    /**
     * @return bool
     */
    private function isRedisEnabled()
    {
        return config('cache.default') === 'redis';
    }

    /**
     * @return bool
     */
    private function isRedisWorking()
    {
        return (string) app('redis')->ping() === 'PONG';
    }
}
