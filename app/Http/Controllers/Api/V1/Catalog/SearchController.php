<?php

// app/Http/Controllers/Api/V1/Catalog/SearchController.php
namespace App\Http\Controllers\Api\V1\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\SearchOffersRequest;
use App\Http\Resources\Catalog\OfferListResource;
use App\Services\Catalog\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(
        private SearchService $searchService
    ) {}

    /**
     * Rechercher offres
     */
    public function search(SearchOffersRequest $request): JsonResponse
    {
        $offers = $this->searchService->searchOffers(
            $request->query ?? '',
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => OfferListResource::collection($offers),
            'meta' => [
                'current_page' => $offers->currentPage(),
                'total' => $offers->total(),
            ],
        ]);
    }

    /**
     * Recherche globale
     */
    public function global(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'La recherche doit contenir au moins 2 caractères',
            ], 422);
        }

        $results = $this->searchService->globalSearch($query);

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * Filtres disponibles
     */
    public function filters(): JsonResponse
    {
        $filters = $this->searchService->getAvailableFilters();

        return response()->json([
            'success' => true,
            'data' => $filters,
        ]);
    }
}