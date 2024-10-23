<?php
namespace V3;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

class Peripheral
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
}
