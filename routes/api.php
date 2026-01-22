<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BlockController;
use App\Http\Controllers\Api\V1\DiscoverController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\GuardianController;
use App\Http\Controllers\Api\V1\InterestController;
use App\Http\Controllers\Api\V1\LookupController;
use App\Http\Controllers\Api\V1\MatchController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\SearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - V1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public Routes
    |--------------------------------------------------------------------------
    */

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('send-otp', [AuthController::class, 'sendOtp']);
        Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('login-otp', [AuthController::class, 'loginWithOtp']);
    });

    // Lookups (public)
    Route::prefix('lookups')->group(function () {
        Route::get('countries', [LookupController::class, 'countries']);
        Route::get('cities', [LookupController::class, 'cities']);
        Route::get('nationalities', [LookupController::class, 'nationalities']);
        Route::get('work-fields', [LookupController::class, 'workFields']);
        Route::get('enums', [LookupController::class, 'enums']);
    });

    // Guardian invitation verification (public)
    Route::post('guardian/verify-invitation', [GuardianController::class, 'verifyInvitation']);
    Route::post('guardian/register', [GuardianController::class, 'register']);

    /*
    |--------------------------------------------------------------------------
    | Protected Routes (Require Authentication)
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('auth/logout', [AuthController::class, 'logout']);

        // Current User
        Route::get('me', [AuthController::class, 'me']);
        Route::put('me', [ProfileController::class, 'update']);
        Route::put('me/password', [ProfileController::class, 'updatePassword']);
        Route::delete('me', [ProfileController::class, 'destroy']);

        // Photos
        Route::post('me/photos', [ProfileController::class, 'uploadPhoto']);
        Route::delete('me/photos/{photo}', [ProfileController::class, 'deletePhoto']);
        Route::put('me/photos/{photo}/primary', [ProfileController::class, 'setPrimaryPhoto']);

        // Guardian (for females)
        Route::post('me/guardian', [ProfileController::class, 'setGuardian']);
        Route::put('me/guardian', [ProfileController::class, 'updateGuardian']);
        Route::delete('me/guardian', [ProfileController::class, 'removeGuardian']);

        // Discovery
        Route::get('discover', [DiscoverController::class, 'index']);
        Route::get('discover/recommendations', [DiscoverController::class, 'recommendations']);

        // Search
        Route::get('search', [SearchController::class, 'index']);
        Route::get('profiles/{user:slug}', [SearchController::class, 'show']); // Use slug for profile URLs

        // Interests
        Route::prefix('interests')->group(function () {
            Route::post('/', [InterestController::class, 'store']);
            Route::get('sent', [InterestController::class, 'sent']);
            Route::get('received', [InterestController::class, 'received']);
            Route::put('{interest}/respond', [InterestController::class, 'respond']);
            Route::delete('{interest}', [InterestController::class, 'destroy']);
        });

        // Matches
        Route::prefix('matches')->group(function () {
            Route::get('/', [MatchController::class, 'index']);
            Route::get('{match}', [MatchController::class, 'show']);
            Route::post('{match}/chat-request', [MatchController::class, 'requestChat']);
            Route::put('{match}/chat-request', [MatchController::class, 'respondToChatRequest']);
        });

        // Conversations (for getting Firebase tokens)
        Route::get('conversations', [MatchController::class, 'conversations']);
        Route::get('conversations/{conversation}', [MatchController::class, 'conversation']);

        // Favorites
        Route::prefix('favorites')->group(function () {
            Route::get('/', [FavoriteController::class, 'index']);
            Route::post('/', [FavoriteController::class, 'store']);
            Route::delete('{userId}', [FavoriteController::class, 'destroy']);
        });

        // Blocks
        Route::prefix('blocks')->group(function () {
            Route::get('/', [BlockController::class, 'index']);
            Route::post('/', [BlockController::class, 'store']);
            Route::delete('{userId}', [BlockController::class, 'destroy']);
        });

        // Reports
        Route::post('reports', [ReportController::class, 'store']);

        // Device Tokens
        Route::post('device-tokens', [ProfileController::class, 'registerDeviceToken']);
        Route::delete('device-tokens/{token}', [ProfileController::class, 'removeDeviceToken']);

        // Notifications
        Route::get('notifications', [ProfileController::class, 'notifications']);
        Route::put('notifications/read-all', [ProfileController::class, 'markAllNotificationsRead']);
        Route::put('notifications/{notification}/read', [ProfileController::class, 'markNotificationRead']);

        /*
        |--------------------------------------------------------------------------
        | Guardian Routes (For users with guardian role)
        |--------------------------------------------------------------------------
        */

        Route::prefix('guardian')->middleware('guardian')->group(function () {
            Route::get('dashboard', [GuardianController::class, 'dashboard']);
            Route::get('chat-requests', [GuardianController::class, 'chatRequests']);
            Route::get('chat-requests/{chatRequest}', [GuardianController::class, 'showChatRequest']);
            Route::put('chat-requests/{chatRequest}', [GuardianController::class, 'respondToChatRequest']);
            Route::get('approved', [GuardianController::class, 'approved']);
            Route::delete('approved/{chatRequest}', [GuardianController::class, 'revokeApproval']);
        });
    });
});

/*
|--------------------------------------------------------------------------
| Health Check
|--------------------------------------------------------------------------
*/

Route::get('health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
});
