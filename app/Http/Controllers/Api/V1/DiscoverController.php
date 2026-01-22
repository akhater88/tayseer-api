<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\MatchingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscoverController extends Controller
{
    public function __construct(
        protected MatchingService $matchingService
    ) {}

    /**
     * Get discovery feed
     * Returns profiles of opposite gender that the user hasn't interacted with
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min($request->query('per_page', 20), 50);

        $profiles = $this->matchingService->getDiscoveryFeed($user, $perPage);

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($profiles),
            'meta' => [
                'current_page' => $profiles->currentPage(),
                'last_page' => $profiles->lastPage(),
                'per_page' => $profiles->perPage(),
                'total' => $profiles->total(),
            ],
        ]);
    }

    /**
     * Get daily recommendations
     * AI/Algorithm-based recommendations based on location, age range, religious level
     */
    public function recommendations(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = min($request->query('limit', 5), 10);

        $recommendations = $this->matchingService->getDailyRecommendations($user, $limit);

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($recommendations),
        ]);
    }
}
