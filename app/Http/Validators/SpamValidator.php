<?php

namespace Coyote\Http\Validators;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

class SpamValidator
{
    const REGEXP_URL = '/((([A-Za-z]{3,9}:(?:\/\/)?)[A-Za-z0-9\.\-]+|(?:www\.)[A-Za-z0-9\.\-]+)((?:\/[\+~%\/\.\w\-_]*)?\??(?:[\-\+=&;%@\.\w_]*)#?(?:[\.\!\/\\\w]*))?)/';

    /**
     * @var Guard
     */
    protected $auth;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @param Guard $auth
     * @param Request $request
     */
    public function __construct(Guard $auth, Request $request)
    {
        $this->auth = $auth;
        $this->request = $request;
    }

    /**
     * @param mixed $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    public function validateSpamLink($attribute, $value, $parameters): bool
    {
        if ($this->isContainUrl($value) === false) {
            return true;
        }

        return $this->auth->check() && $this->auth->user()->reputation >= $parameters[0];
    }

    /**
     * @param mixed $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    public function validateSpamForeignLink($attribute, $value, $parameters): bool
    {
        if (!$this->request->server('HTTP_CF_IPCOUNTRY') || 'PL' === $this->request->server('HTTP_CF_IPCOUNTRY')) {
            return true;
        }

        if ($this->isContainUrl($value) === false) {
            return true;
        }

        return $this->auth->check() && $this->auth->user()->posts >= $parameters[0];
    }

    /**
     * @param mixed $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    public function validateSpamChinese($attribute, $value, $parameters): bool
    {
        if (!$this->isContainChinese($value)) {
            return true;
        }

        return $this->auth->check() && $this->auth->user()->reputation >= $parameters[0];
    }

    /**
     * @return bool
     */
    public function validateBlacklistHost(): bool
    {
        if ($this->auth->check()) {
            return true;
        }

        $clientHost = $this->request->getClientHost();

        if (empty($clientHost)) {
            return true;
        }

        foreach (config('app.blacklist_host') as $host) {
            if (str_contains($clientHost, $host)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $text
     * @return bool
     */
    private function isContainUrl(string $text): bool
    {
        if (trim($text) === '') {
            return false;
        }

        return (bool) preg_match(self::REGEXP_URL, $text);
    }

    /**
     * @param string $text
     * @return bool
     */
    private function isContainChinese(string $text): bool
    {
        return ((bool) preg_match("/\p{Hangul}+/u", $text)) || ((bool) preg_match("/\p{Han}+/u", $text));
    }
}
