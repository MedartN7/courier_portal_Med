<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;  
use Illuminate\Support\Facades\Auth;  
use Illuminate\Support\Facades\Hash;  
use Illuminate\Support\Str; 
use Exception;  

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        // dd(Socialite::driver('google')->redirect()->getTargetUrl());
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            dd( $googleUser, $googleUser->user );
            // if ( ( isset( $googleUser->user[ 'verified_email' ] ) && $googleUser->user[ 'verified_email' ] === true ) || 
            //      ( isset( $googleUser->user[ 'email_verified' ] ) && $googleUser->user[ 'email_verified' ] === true ) ) {
            //         if ( $googleUser->user[] ) {

            //         }
            // } else {
            //     return redirect('/login')->with('error', 'Konto google nie jest zweryfikowane, nie możemy obsłużyć logowania');
            // }
            $user = User::updateOrCreate(
                ['email' => $googleUser->email],
                [
                    'name' => $googleUser->name,
                    'google_id' => $googleUser->id,
                    'password' => Hash::make(Str::random(24))
                ]
            );
            
            Auth::login($user);
            
            return redirect('/dashboard');
            
        } catch (Exception $e) {
            return redirect('/login')->with('error', 'Wystąpił błąd podczas logowania przez Google');
        }
    }
}
