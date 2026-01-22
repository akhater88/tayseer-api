<?php

namespace App\Http\Middleware;

use App\Enums\UserType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GuardianMiddleware
{
    /**
     * Handle an incoming request.
     * Verifies that the authenticated user is a guardian.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح',
            ], 401);
        }

        if ($user->user_type !== UserType::Guardian) {
            return response()->json([
                'success' => false,
                'message' => 'هذا الإجراء مخصص لأولياء الأمور فقط',
            ], 403);
        }

        // Check if guardian has active ward relationship
        $hasActiveWard = $user->wards()->where('status', 'active')->exists();

        if (!$hasActiveWard) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد موكلة مرتبطة بحسابك',
            ], 403);
        }

        return $next($request);
    }
}
