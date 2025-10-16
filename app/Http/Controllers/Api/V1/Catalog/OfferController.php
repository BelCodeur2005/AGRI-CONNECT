<?php

// app/Http/Controllers/Api/V1/Catalog/OfferController.php
namespace App\Http\Controllers\Api\V1\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StoreOfferRequest;
use App\Http\Requests\Catalog\UpdateOfferRequest;
use App\Http\Resources\Catalog\OfferDetailResource;
use App\Http\Resources\Catalog\OfferListResource;
use App\Models\Offer;
use App\Services\Catalog\OfferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function __construct(
        private OfferService $offerService
    ) {}

    /**
     * Liste des offres du producteur
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Offer::class);

        $producer = $request->user()->producer;
        $offers = $this->offerService->getProducerOffers($producer, $request->all());

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
     * Créer offre
     */
    public function store(StoreOfferRequest $request): JsonResponse
    {
        $this->authorize('create', Offer::class);

        $producer = $request->user()->producer;
        $offer = $this->offerService->create($producer, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Offre créée avec succès',
            'data' => new OfferDetailResource($offer),
        ], 201);
    }

    /**
     * Détail d'une offre
     */
    public function show(Offer $offer): JsonResponse
    {
        $this->authorize('view', $offer);

        // Incrémenter vues
        $offer->incrementViews();

        return response()->json([
            'success' => true,
            'data' => new OfferDetailResource($offer->load(['product', 'producer.user', 'location'])),
        ]);
    }

    /**
     * Mettre à jour offre
     */
    public function update(UpdateOfferRequest $request, Offer $offer): JsonResponse
    {
        $this->authorize('update', $offer);

        $producer = $request->user()->producer;
        $offer = $this->offerService->update($offer, $producer, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Offre mise à jour',
            'data' => new OfferDetailResource($offer),
        ]);
    }

    /**
     * Supprimer offre
     */
    public function destroy(Offer $offer): JsonResponse
    {
        $this->authorize('delete', $offer);

        $producer = auth()->user()->producer;
        $this->offerService->delete($offer, $producer);

        return response()->json([
            'success' => true,
            'message' => 'Offre supprimée',
        ]);
    }

    /**
     * Statistiques d'une offre
     */
    public function statistics(Offer $offer): JsonResponse
    {
        $this->authorize('view', $offer);

        $stats = $this->offerService->getStatistics($offer);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
