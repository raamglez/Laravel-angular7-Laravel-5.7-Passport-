<?php

namespace App\Http\Controllers\API\Auth;

use App\Notifications\PasswordResetRecovery;
use App\Notifications\PasswordResetSuccess;
use App\PasswordReset;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PasswordResetController extends Controller
{
    public function create(Request $request)
    {
        $request->validate(['email' => 'required|string|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(
                [
                    'error'   => 'user_not_found',
                    'message' => 'No podemos encontrar un usuario con esa dirección de correo electrónico.'
                ], 404);
        }

        if ($user->licensed_at < Carbon::now()) {
            return response()->json(
                [
                    "error"   => "account_expired",
                    "message" => "Su cuenta ha expirado."
                ], 403);
        }

        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => str_random(60)
            ]
        );

        if ($user && $passwordReset) {
            $user->notify(new PasswordResetRecovery($passwordReset->token));
        }

        return response()->json(['message' => 'Hemos enviado por correo electrónico el enlace para restablecer su contraseña!']);
    }

    public function find($token)
    {
        $passwordReset = PasswordReset::where('token', $token)->first();

        if (!$passwordReset) {
            return response()->json(
                [
                    'error'   => 'invalid_token',
                    'message' => 'Este token de restablecimiento de contraseña no es válido.'
                ], 404);
        }

        if ($passwordReset->user->licensed_at < Carbon::now()) {
            return response()->json(
                [
                    "error"   => "account_expired",
                    "message" => "Su cuenta ha expirado."
                ], 403);
        }

        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();
            return response()->json(
                [
                    'error'   => 'invalid_token',
                    'message' => 'Este token de restablecimiento de contraseña no es válido'
                ], 404);
        }

        return response()->json(
            [
                "id"         => $passwordReset->id,
                "email"      => $passwordReset->email,
                "usuario"    => $passwordReset->user->usuario,
                "token"      => $passwordReset->token,
                "created_at" => $passwordReset->created_at,
                "updated_at" => $passwordReset->updated_at,

            ]
        );
    }

    public function reset(Request $request)
    {
        $request->validate(
            [
                'email'    => 'required|string|email',
                'password' => 'required|string',
                'token'    => 'required|string'
            ]
        );

        $passwordReset = PasswordReset::where([['token', $request->token], ['email', $request->email]])->first();

        if (!$passwordReset) {
            return response()->json(
                [
                    'error'   => 'invalid_token',
                    'message' => 'Este token de restablecimiento de contraseña no es válido'
                ], 404);
        }

        $user = User::where('email', $passwordReset->email)->first();

        if (!$user) {
            return response()->json(
                [
                    'error'   => 'user_not_found',
                    'message' => 'No podemos encontrar un usuario con esa dirección de correo electrónico. '
                ], 404);
        }

        if ($user->licensed_at < Carbon::now()) {
            return response()->json(
                [
                    "error"   => "account_expired",
                    "message" => "Su cuenta ha expirado."
                ], 403);
        }

        $user->password = bcrypt($request->password);
        $user->save();
        $passwordReset->delete();
        $user->notify(new PasswordResetSuccess());
        return response()->json($user);
    }
}
