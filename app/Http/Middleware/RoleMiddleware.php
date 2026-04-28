<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'data' => null,
            ], 401);
        }

        if ($user->role !== $role) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'data' => null,
            ], 403);
        }

        if ($user->status === 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'حسابك قيد المراجعة، يرجى الانتظار حتى يتم قبول طلبك',
                'data' => null,
            ], 403);
        }

        if ($user->status === 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'تم رفض طلبك، يرجى التواصل مع الإدارة',
                'data' => null,
            ], 403);
        }

        if ($user->status === 'suspended') {
            return response()->json([
                'success' => false,
                'message' => 'تم تعليق حسابك، يرجى التواصل مع الإدارة',
                'data' => null,
            ], 403);
        }

        return $next($request);
    }
}
