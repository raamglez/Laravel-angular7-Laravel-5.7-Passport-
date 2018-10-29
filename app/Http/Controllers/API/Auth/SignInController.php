<?php

namespace App\Http\Controllers\API\Auth;

use App\User;
use Carbon\Carbon;
use Psr\Http\Message\ServerRequestInterface;

class SignInController extends \Laravel\Passport\Http\Controllers\AccessTokenController
{
    public function signIn(ServerRequestInterface $request)
    {
        $httpRequest = request();

        $user = User::where('usuario', $httpRequest->username)->first();

        if (!$user) {
            return response()->json(
                [
                    "error"   => "user_not_found",
                    "message" => "Esta cuenta no existe."
                ], 404);
        }

        if ($user->licensed_at < Carbon::now()) {
            return response()->json(
                [
                    "error"   => "account_expired",
                    "message" => "Su cuenta ha expirado."
                ], 403);
        }

        return $this->issueToken($request);
    }
}
