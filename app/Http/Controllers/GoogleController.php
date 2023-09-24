<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;


class GoogleController extends Controller
{
    public function OAuthGoogle()
    {
        $scopes = [
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile',
            'openid',
            'https://www.googleapis.com/auth/calendar'
        ];

        return Socialite::driver('google')
            ->stateless()
            ->scopes($scopes)
            ->with(["access_type" => "offline", "prompt" => "consent select_account"])
            ->redirect();
    }

    public function OAuthGoogleCallback()
    {
        $user = Socialite::driver('google')->stateless()->user();

        User::where('email', $user->email)->update(['google_access_token'=> $user->token, 'google_refresh_token'=>$user->refreshToken]);

    }
}
