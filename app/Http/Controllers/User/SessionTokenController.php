<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionTokenController extends Controller
{
    public function __construct()
    {
        // tymczasowe rozwiazanie - nadpisujemy kontruktor z klasy bazowej
    }

    public function generateToken(): JsonResponse
    {
        $secret = config('app.key');

        if (auth()->guest()) {
            return response()->json(['error' => 'No user logged in'], 401);
        }

        $token = auth()->user()->id . '|' . \strToTime('+ 1 day');
        $signedToken = $token . '|' . hash_hmac('sha256', $token, $secret);

        return response()
          ->json(['token' => $signedToken]);
    }

    public function verifyToken(Request $request): JsonResponse
    {
        $secret = config('app.key');
        $input = $request->input('token');
        $fragments = explode('|', $input);
        $token = $fragments[0] . '|' . $fragments[1];
        $signature = $fragments[2];
        $signatureCheck = hash_hmac('sha256', $token, $secret);

        return response()
          ->json(['valid' => $signatureCheck === $signature]);
    }
}
