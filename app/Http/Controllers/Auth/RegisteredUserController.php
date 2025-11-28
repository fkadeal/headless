<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules; 
use Illuminate\Http\JsonResponse;


class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
public function store_v1(Request $request): JsonResponse
{
    // Validate request
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    // Create user
    $user = User::create([
        'name' => $request->input('name'),
        'email' => $request->input('email'),
        'password' => Hash::make($request->input('password')),
    ]);

    // Fire Registered event
    event(new Registered($user));

    logger('User registered'.json_encode($user));

    // Log in user
    Auth::login($user);

    // Create API token
    $token = $user->createToken('api-token')->plainTextToken;

    // Return JSON response
    return response()->json([
        'success' => true,
        'message' => 'User registered successfully',
        'data' => [
            'user' => $user,
            'token' => $token,
        ],
    ]);
}
public function store(Request $request): JsonResponse
{
    $request->validate([
        'email' => ['required', 'string', 'email', 'max:255'],
        'password' => ['required', 'string'],
        'name' => ['sometimes', 'string', 'max:255'], // Only required for new users
    ]);

    // Check if user exists
    $user = User::where('email', $request->email)->first();

    // ========== LOGIN FLOW ==========
    if ($user) {
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect password',
            ], 401);
        }

        // Login existing user
        Auth::login($user);
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ]);
    }

    // ========== REGISTER FLOW ==========
    $newUser = User::create([
        'name' => $request->input('name') ?? 'New User',
        'email' => $request->input('email'),
        'password' => Hash::make($request->input('password')),
    ]);

    event(new Registered($newUser));
    Auth::login($newUser);

    $token = $newUser->createToken('api-token')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'User registered successfully',
        'data' => [
            'user' => $newUser,
            'token' => $token,
        ],
    ]);
}
}
