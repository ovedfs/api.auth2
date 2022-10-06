<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    public function forgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function forgotPasswordValidate(Request $request)
    {
        $request->validate(['email' => 'required|email']);
    
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'status' => __($status)
            ]);
        }

        throw ValidationException::withMessages([
            'email' => __($status)
        ]);
    }

    public function resetPasswordForm($token) {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function resetPasswordValidate(Request $request) {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);
        
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
        
                $user->save();
        
                event(new PasswordReset($user));
            }
        );
        
        if($status === Password::PASSWORD_RESET) {
            return response()->json([
                'status' => __($status)
            ]);
        }

        throw ValidationException::withMessages([
            'email' => __($status)
        ]);
    }
}