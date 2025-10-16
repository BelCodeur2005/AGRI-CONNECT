<?php

// app/Http/Controllers/Api/V1/Catalog/OfferBrowseController.php
namespace App\Http\Controllers\Api\V1\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\BrowseOffersRequest;
use App\Http\Resources\Catalog\OfferListResource;
use App\Models\Offer;
use Illuminate\Http\JsonResponse;

class OfferBrowseController extends Controller
{
    public function __invoke(BrowseOffersRequest $request): JsonResponse
    {
        $query = Offer::active()
            ->with(['product', 'producer.user', 'location']);

        // Filtres
        if ($request->category_id) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        if ($request->location_id) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->organic_only) {
            $query->organic();
        }

        // Tri
        match($request->sort_by) {
            'price_asc' => $query->orderBy('price_per_unit', 'asc'),
            'price_desc' => $query->orderBy('price_per_unit', 'desc'),
            'quantity' => $query->orderBy('quantity_available', 'desc'),
            'rating' => $query->join('producers', 'offers.producer_id', '=', 'producers.id')
                             ->orderBy('producers.average_rating', 'desc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $offers = $query->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => OfferListResource::collection($offers),
            'meta' => [
                'current_page' => $offers->currentPage(),
                'total' => $offers->total(),
            ],
        ]);
    }
}
