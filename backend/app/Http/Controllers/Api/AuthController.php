<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{


// * Connexion par session Sanctum SPA.

    /*
    |--------------------------------------------------------------------------
    | LOGIN
    |--------------------------------------------------------------------------
    */
    public function login(Request $request)
    {
        $credentials = $request->validate([

            'email' => 'required|email',

            'password' => 'required'
        ]);

        if (!Auth::attempt($credentials)) {

            return response()->json([
                'message' => 'Identifiants invalides'
            ], 401);
        }

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Connecté avec succès',
            'user' => Auth::user()
        ]);
    }
     /*
    |--------------------------------------------------------------------------
    | REGISTER
    |--------------------------------------------------------------------------
    */
    public function register(Request $request)
{
    $validated = $request->validate([
        'first_name' => ['required', 'string', 'max:255'],
        'last_name'  => ['required', 'string', 'max:255'],
        'email'      => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'phone'      => ['nullable', 'string', 'max:20'],
        'password'   => ['required', 'string', 'min:8', 'confirmed'],
    ]);

    $user = User::create([
        'first_name' => $validated['first_name'],
        'last_name'  => $validated['last_name'],
        'email'      => $validated['email'],
        'phone'      => $validated['phone'] ?? null,
        'password'   => Hash::make($validated['password']),
    ]);

    return response()->json([
        'user' => $user,
    ], 201);
}


    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Déconnecté avec succès'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CURRENT USER
    |--------------------------------------------------------------------------
    */
    public function me()
    {
        return response()->json(Auth::user());
    }
}
