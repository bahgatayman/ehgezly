<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

// signup
    public function signup(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'phone' => 'required|unique:users',
        'password' => 'required|min:6',
        'role' => 'required|in:customer,courtowner',
    ]);

    $user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'phone' => $request->phone,
    'password' => Hash::make($request->password),
    'role' => $request->role,
    'status' => 'active'
]);

$user->notify(new WelcomeNotification());

$token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'Registered',
        'user' => $user,
        'token' => $token
    ]);
}

// login
public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'message' => 'Invalid credentials'
        ], 401);
    }

    if ($user->status === 'suspended') {
        return response()->json([
            'message' => 'Account suspended'
        ], 403);
    }

    //
    $user->tokens()->delete();

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'Login success',
        'user' => $user,
        'token' => $token
    ]);
}

public function forgotPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
    ],[
        'email.exists' => 'This email is not registered.'
    ]);

    $status = Password::sendResetLink(
        $request->only('email'),
        function ($user, $token) {
            $user->notify(new ResetPasswordNotification($token));
        }
    );

    if ($status === Password::RESET_LINK_SENT) {
        return response()->json([
            'success' => true,
            'message' => 'Password reset link sent successfully.'
        ]);
    }

    if ($status === Password::INVALID_USER) {
        return response()->json([
            'success' => false,
            'message' => 'Email not found.'
        ], 404);
    }

    return response()->json([
        'success' => false,
        'message' => 'Please try again later.'
    ], 500);
}

public function resetPassword(Request $request)
{
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|confirmed|min:6',
    ],[
        'password.confirmed' => 'Password confirmation does not match.',
        'password.min' => 'Password must be at least 6 characters.',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {

            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();

            // logout from all devices (optional but recommended)
            $user->tokens()->delete();
        }
    );

    if ($status === Password::PASSWORD_RESET) {
        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully.'
        ]);
    }

    if ($status === Password::INVALID_TOKEN) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired reset token.'
        ], 400);
    }

    if ($status === Password::INVALID_USER) {
        return response()->json([
            'success' => false,
            'message' => 'User not found.'
        ], 404);
    }

    return response()->json([
        'success' => false,
        'message' => 'Password reset failed.'
    ], 500);
}

}
