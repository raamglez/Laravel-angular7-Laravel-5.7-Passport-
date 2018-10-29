<?php

namespace App\Http\Controllers\API\Auth;

use App\Notifications\BSNewUser;
use App\Notifications\SignUpSuccess;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class SignUpController extends Controller
{
    public function signUp(Request $request)
    {
        Validator::make(
            request()->all(),
            [
                'nombre'   => 'required|string|max:255',
                'usuario'  => 'required|min:4|max:20',
                'correo'   => 'required|string|email|max:255',
                'password' => 'required|string|min:6',
            ]
        );

        if (User::where('usuario', $request->usuario)->first()) {
            return response()->json(
                [
                    "errors" => ["usuario" => "Este usuario ya se encuentra en uso"]
                ]
                , 401);
        }
        if (User::where('email', $request->correo)->first()) {
            return response()->json(
                [
                    "errors" => ["correo" => "Este correo ya se encuentra en uso"]
                ]
                ,401);
        }

        $user     = User::create(
            [
                'nombre'      => $request->nombre,
                'usuario'     => $request->usuario,
                'email'       => $request->correo,
                'password'    => bcrypt($request->password),
                'licensed_at' => Carbon::now()->addDays(30),
            ]
        );

        $user->notify(new SignUpSuccess());

        (new User)
            ->forceFill(
                [
                    'name'  => "BSTI Soporte",
                    'email' => "soporte@bullsystemti.com.mx",
                ]
            )
            ->notify(new BSNewUser());


        return response()->json(["message" => "Se ha registrado correctamente"], 201);
    }
}
