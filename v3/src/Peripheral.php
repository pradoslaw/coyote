<?php
namespace V3;

use Coyote\Http\Validators\UserValidator;
use Coyote\User;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use TwigBridge;
use V3\Feature\Register\RegisterGate;

class Peripheral implements RegisterGate
{
    public static function addGetRoute(string $string, callable $handler): void
    {
        Route::get($string, $handler);
    }

    public static function addPostRoute(string $string, callable $handler): void
    {
        Route::post($string, $handler);
    }

    public static function renderTwig(string $templateName, array $data): string
    {
        /** @var TwigBridge\Bridge $twig */
        $twig = app('twig');
        $twig->enableStrictVariables();
        return view("v3.$templateName", $data)->render();
    }

    public static function resourceUrl(string $string): string
    {
        return cdn($string);
    }

    public static function httpRequestField(string $field): ?string
    {
        $value = Request::get($field);
        if ($value === null) {
            abort(422);
        }
        return $value;
    }

    public static function httpRequestQuery(string $string): ?string
    {
        return Request::query($string);
    }

    public function isUsernameAvailable(string $login): bool
    {
        /** @var UserValidator $validator */
        $validator = app(UserValidator::class);
        return $validator->validateUnique('', $login, []);
    }

    public function isEmailAvailable(string $password): bool
    {
        return !User::query()->where('email', '=', $password)->exists();
    }

    public function createUserAndLogin(string $login, string $email, string $password): void
    {
    }

    public function isUsernameCorrect(string $login): bool
    {
        /** @var UserValidator $validator */
        $validator = app(UserValidator::class);
        return $validator->validateName('', $login);
    }
}
