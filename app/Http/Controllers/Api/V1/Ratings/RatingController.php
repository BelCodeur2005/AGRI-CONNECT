<?php

// app/Http/Controllers/Api/V1/Ratings/RatingController.php
namespace App\Http\Controllers\Api\V1\Ratings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ratings\StoreRatingRequest;
use App\Http\Requests\Ratings\UpdateRatingRequest;
use App\Http\Resources\Ratings\RatingResource;
use App\Models\Order;
use App\Models\Rating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    /**
     * Noter une commande
     */
    public function store(StoreRatingRequest $request, Order $order): JsonResponse
    {
        $this->authorize('rate', $order);

        $rating = Rating::create([
            'order_id' => $order->id,
            'rater_id' => auth()->id(),
            'rateable_type' => $request->rateable_type,
            'rateable_id' => $request->rateable_id,
            'overall_score' => $request->overall_score,
            'quality_score' => $request->quality_score,
            'punctuality_score' => $request->punctuality_score,
            'communication_score' => $request->communication_score,
            'packaging_score' => $request->packaging_score,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Évaluation enregistrée',
            'data' => new RatingResource($rating),
        ], 201);
    }

    /**
     * Mettre à jour évaluation
     */
    public function update(UpdateRatingRequest $request, Rating $rating): JsonResponse
    {
        $rating->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Évaluation mise à jour',
            'data' => new RatingResource($rating),
        ]);
    }

    /**
     * Mes évaluations
     */
    public function index(Request $request): JsonResponse
    {
        $ratings = Rating::where('rater_id', auth()->id())
            ->with(['order', 'rateable'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => RatingResource::collection($ratings),
        ]);
    }

    /**
     * Évaluations reçues
     */
    public function received(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->getProfile();

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profil introuvable',
            ], 404);
        }

        $ratings = $profile->ratings()
            ->with('rater')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => RatingResource::collection($ratings),
        ]);
    }
}