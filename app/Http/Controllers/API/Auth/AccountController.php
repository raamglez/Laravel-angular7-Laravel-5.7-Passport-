<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Resources\UserResource;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{

    public function account()
    {
        return new UserResource(User::find(auth()->user()->id));
    }

    public function update(Request $request)
    {
        $rules = [
            'nombre'  => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore(auth()->user()->id)]
        ];
        if ($request->filled('password')) {
            $rules['password'] = ['min:6'];
        }

        $request->validate($rules);

        $user           = User::find(auth()->user()->id);
        $user->nombre   = $request->nombre;
        $user->email    = $request->email;

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return new UserResource($user);
    }
    
    public function signOut()
    {
        $result = Auth::user()->AauthAcessToken()->delete();
        return response()->json(['data' => $result], 200);
    }
}
