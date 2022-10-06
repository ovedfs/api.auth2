<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed'
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        // $user->email_verified_at = now();
        $user->remember_token = Str::random(10);
        
        if($user->save()) {
            $token = $user->createToken('auth_token')->plainTextToken;

            event(new Registered($user));

            return response()->json([
                'status' => 1,
                'message' => 'Registro de usuario correcto, para usar tu cuenta debes validarla desde el correo que te enviamos',
                'user' => $user,
                'token' => $token
            ]);
        }

        return response()->json([
            'status' => 0,
            'message' => 'Registro incorrecto de usuario correcto'
        ]);
    }

    public function login(Request $request)
    {
        if(!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => 0,
                'message' => 'Inicio de sesión incorrecto'
            ]);
        }

        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 1,
            'message' => 'Inicio de sesión correcto',
            'user' => $user,
            'token' => $token
        ]);
    }

    public function profile()
    {
        return response()->json([
            'status' => 1,
            'message' => 'Perfil de usuario',
            'user' => auth()->user(),
        ]);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            'status' => 1,
            'message' => 'Sesión finalizada'
        ]);
    }

    public function verifyEmail(Request $request)
    {
        return response()->json([
            'message' => 'Debes verificar tu cuenta, para ello sigue las indicaciones del correo que te enviamos'
        ]);
    }

    public function verifySuccess(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return response()->json([
            'message' => 'Tu correo ha sido validado, bienvenido!'
        ]);
    }

    public function resendVerifyEmail(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Se te ha enviado un nuevo correo para verificar tu cuenta'
        ]);
    }
}