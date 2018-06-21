<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;



class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'username',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public static function getPasswordResetToken($email)
    {
        return \DB::table(config('auth.passwords.users.table'))
            ->select('token')
            ->where('email', $email)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public static function removePasswordResetTokens($email)
    {
        return \DB::table(config('auth.passwords.users.table'))
            ->where('email', $email)
            ->delete();
    }
}
