<?php

// app/Http/Controllers/Api/FavoriteController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Offer;
use App\Models\Producer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Mes favoris
     */
    public function index(Request $request): JsonResponse
    {
        $favorites = Favorite::where('user_id', auth()->id())
            ->with('favoriteable')
            ->when($request->type, function($query, $type) {
                $modelClass = $type === 'producer' ? Producer::class : Offer::class;
                $query->where('favoriteable_type', $modelClass);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $favorites,
        ]);
    }

    /**
     * Ajouter aux favoris
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:offer,producer',
            'id' => 'required|integer',
        ]);

        $modelClass = $request->type === 'producer' ? Producer::class : Offer::class;
        $item = $modelClass::findOrFail($request->id);

        // Vérifier si déjà en favori
        $existing = Favorite::where('user_id', auth()->id())
            ->where('favoriteable_type', $modelClass)
            ->where('favoriteable_id', $item->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Déjà dans vos favoris',
            ], 400);
        }

        Favorite::create([
            'user_id' => auth()->id(),
            'favoriteable_type' => $modelClass,
            'favoriteable_id' => $item->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ajouté aux favoris',
        ]);
    }

    /**
     * Retirer des favoris
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:offer,producer',
            'id' => 'required|integer',
        ]);

        $modelClass = $request->type === 'producer' ? Producer::class : Offer::class;

        $favorite = Favorite::where('user_id', auth()->id())
            ->where('favoriteable_type', $modelClass)
            ->where('favoriteable_id', $request->id)
            ->first();

        if (!$favorite) {
            return response()->json([
                'success' => false,
                'message' => 'Non trouvé dans vos favoris',
            ], 404);
        }

        $favorite->delete();

        return response()->json([
            'success' => true,
            'message' => 'Retiré des favoris',
        ]);
    }

    /**
     * Vérifier si en favoris
     */
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:offer,producer',
            'id' => 'required|integer',
        ]);

        $modelClass = $request->type === 'producer' ? Producer::class : Offer::class;

        $isFavorite = Favorite::where('user_id', auth()->id())
            ->where('favoriteable_type', $modelClass)
            ->where('favoriteable_id', $request->id)
            ->exists();

        return response()->json([
            'success' => true,
            'data' => ['is_favorite' => $isFavorite],
        ]);
    }
}
