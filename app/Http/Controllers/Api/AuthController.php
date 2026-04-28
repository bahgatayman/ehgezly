<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Courtowner;
use App\Models\Customer;
use App\Models\Notification;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

// signup
    public function signup(Request $request)
{
    $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'phone' => 'required|unique:users',
        'password' => 'required|min:6',
        'role' => 'required|in:customer,courtowner,admin',
    ];

    if ($request->input('role') === 'courtowner') {
        $rules['ownership_proof_url'] = 'required|image|mimes:jpeg,png,jpg,webp|max:5120';
    }

    $validated = $request->validate($rules);

    if ($validated['role'] === 'admin') {
        return response()->json([
            'success' => false,
            'message' => 'لا يمكن التسجيل كمسؤول',
            'data' => null,
        ], 403);
    }

    if ($validated['role'] === 'customer') {
        $result = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => 'customer',
                'status' => 'active',
            ]);

            Customer::create([
                'user_id' => $user->id,
                'can_book' => true,
            ]);

            $user->notify(new WelcomeNotification());

            $token = $user->createToken('auth_token')->plainTextToken;

            return [$user, $token];
        });

        return response()->json([
            'success' => true,
            'message' => 'Registered',
            'data' => [
                'user' => $result[0],
                'token' => $result[1],
            ],
        ]);
    }

    $user = DB::transaction(function () use ($request, $validated) {
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => 'courtowner',
            'status' => 'pending',
        ]);

        $path = $request->file('ownership_proof_url')->store("ownership_proofs/{$user->id}", 'public');
        $ownershipUrl = Storage::url($path);

        Courtowner::create([
            'user_id' => $user->id,
            'ownership_proof_url' => $ownershipUrl,
            'commission_percentage' => 5.00,
            'total_revenue' => 0,
            'app_due_amount' => 0,
            'remaining_balance' => 0,
        ]);

        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'title' => 'طلب تسجيل مالك ملعب جديد',
                'message' => "قام {$user->name} بالتسجيل كمالك ملعب ويحتاج موافقتك",
                'type' => 'new_owner_request',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
            ]);
        }

        return $user;
    });

    return response()->json([
        'success' => true,
        'message' => 'تم إرسال طلبك بنجاح، سيتم مراجعته من قبل الإدارة',
        'data' => [
            'user' => $user,
        ],
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
