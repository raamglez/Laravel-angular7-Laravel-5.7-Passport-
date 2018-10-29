<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    public $timestamps = false;
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'nombre', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function findForPassport($identifier)
    {
        return $this->orWhere('email', $identifier)->orWhere('usuario', $identifier)->first();
    }

    public function AauthAcessToken()
    {
        return $this->hasMany('App\OauthAccessToken');
    }
}
