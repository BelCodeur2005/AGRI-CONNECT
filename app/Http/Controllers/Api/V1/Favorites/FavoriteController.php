<?php

// app/Http/Controllers/Api/V1/Favorites/FavoriteController.php
namespace App\Http\Controllers\Api\V1\Favorites;

use App\Http\Controllers\Controller;
use App\Http\Resources\Catalog\OfferListResource;
use App\Models\Favorite;
use App\Models\Offer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Mes favoris
     */
    public function index(Request $request): JsonResponse
    {
        $favorites = $request->user()->favorites()
            ->where('favoriteable_type', Offer::class)
            ->with('favoriteable.product', 'favoriteable.producer.user')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => OfferListResource::collection($favorites->pluck('favoriteable')),
        ]);
    }

    /**
     * Ajouter aux favoris
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'offer_id' => 'required|exists:offers,id',
        ]);

        $favorite = Favorite::firstOrCreate([
            'user_id' => auth()->id(),
            'favoriteable_type' => Offer::class,
            'favoriteable_id' => $request->offer_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => $favorite->wasRecentlyCreated 
                ? 'Ajouté aux favoris' 
                : 'Déjà dans les favoris',
        ], $favorite->wasRecentlyCreated ? 201 : 200);
    }

    /**
     * Retirer des favoris
     */
    public function destroy(Offer $offer): JsonResponse
    {
        Favorite::where('user_id', auth()->id())
            ->where('favoriteable_type', Offer::class)
            ->where('favoriteable_id', $offer->id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Retiré des favoris',
        ]);
    }
}