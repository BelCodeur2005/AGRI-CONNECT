<?php

// app/Services/Catalog/OfferService.php
namespace App\Services\Catalog;

use App\Models\Producer;
use App\Models\Offer;
use App\Models\Product;
use App\Exceptions\Catalog\OfferExpiredException;
use Illuminate\Support\Facades\Storage;

class OfferService
{
    /**
     * Créer une offre
     */
    public function create(Producer $producer, array $data): Offer
    {
        // Vérifier que le producteur peut créer des offres
        if (!$producer->canCreateOffers()) {
            throw new \Exception('Votre compte doit être vérifié pour créer des offres');
        }

        // Upload photos
        $photos = [];
        if (isset($data['photos']) && is_array($data['photos'])) {
            foreach ($data['photos'] as $photo) {
                $path = $photo->store('offers', 'public');
                $photos[] = $path;
            }
        }

        // Créer offre
        $offer = Offer::create([
            'producer_id' => $producer->id,
            'product_id' => $data['product_id'],
            'location_id' => $data['location_id'] ?? $producer->location_id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'quantity_available' => $data['quantity_available'],
            'min_order_quantity' => $data['min_order_quantity'] ?? null,
            'price_per_unit' => $data['price_per_unit'],
            'harvest_date' => $data['harvest_date'] ?? null,
            'available_from' => $data['available_from'],
            'available_until' => $data['available_until'],
            'photos' => $photos,
            'quality_grade' => $data['quality_grade'] ?? null,
            'organic' => $data['organic'] ?? false,
            'status' => 'active',
        ]);

        return $offer->load(['product', 'location']);
    }

    /**
     * Mettre à jour une offre
     */
    public function update(Offer $offer, Producer $producer, array $data): Offer
    {
        // Vérifier propriété
        if ($offer->producer_id !== $producer->id) {
            throw new \Exception('Non autorisé');
        }

        // Vérifier pas de commandes actives
        if ($offer->orderItems()->whereIn('status', ['confirmed', 'ready', 'collected'])->exists()) {
            throw new \Exception('Impossible de modifier une offre avec des commandes en cours');
        }

        // Upload nouvelles photos si présentes
        $photos = $offer->photos ?? [];
        if (isset($data['photos']) && is_array($data['photos'])) {
            foreach ($data['photos'] as $photo) {
                if (is_file($photo)) {
                    $path = $photo->store('offers', 'public');
                    $photos[] = $path;
                }
            }
        }

        // Mettre à jour
        $offer->update([
            'product_id' => $data['product_id'] ?? $offer->product_id,
            'location_id' => $data['location_id'] ?? $offer->location_id,
            'title' => $data['title'] ?? $offer->title,
            'description' => $data['description'] ?? $offer->description,
            'quantity_available' => $data['quantity_available'] ?? $offer->quantity_available,
            'min_order_quantity' => $data['min_order_quantity'] ?? $offer->min_order_quantity,
            'price_per_unit' => $data['price_per_unit'] ?? $offer->price_per_unit,
            'harvest_date' => $data['harvest_date'] ?? $offer->harvest_date,
            'available_from' => $data['available_from'] ?? $offer->available_from,
            'available_until' => $data['available_until'] ?? $offer->available_until,
            'photos' => $photos,
            'quality_grade' => $data['quality_grade'] ?? $offer->quality_grade,
            'organic' => $data['organic'] ?? $offer->organic,
        ]);

        return $offer->fresh(['product', 'location']);
    }

    /**
     * Supprimer une offre
     */
    public function delete(Offer $offer, Producer $producer): void
    {
        if ($offer->producer_id !== $producer->id) {
            throw new \Exception('Non autorisé');
        }

        // Vérifier pas de commandes actives
        if ($offer->orderItems()->active()->exists()) {
            throw new \Exception('Impossible de supprimer une offre avec des commandes actives');
        }

        // Supprimer photos
        if ($offer->photos) {
            foreach ($offer->photos as $photo) {
                Storage::disk('public')->delete($photo);
            }
        }

        $offer->delete();
    }

    /**
     * Activer/Désactiver une offre
     */
    public function toggleStatus(Offer $offer, Producer $producer): Offer
    {
        if ($offer->producer_id !== $producer->id) {
            throw new \Exception('Non autorisé');
        }

        $newStatus = $offer->status->value === 'active' ? 'inactive' : 'active';
        $offer->update(['status' => $newStatus]);

        return $offer;
    }

    /**
     * Ajuster stock
     */
    public function adjustStock(Offer $offer, float $newQuantity, string $reason): void
    {
        $oldQuantity = $offer->quantity_available;

        $offer->stockMovements()->create([
            'type' => 'adjustment',
            'quantity' => abs($newQuantity - $oldQuantity),
            'quantity_before' => $oldQuantity,
            'quantity_after' => $newQuantity,
            'reason' => $reason,
            'created_by' => auth()->id(),
        ]);

        $offer->update(['quantity_available' => $newQuantity]);
        $offer->updateStatus();
    }

    /**
     * Obtenir offres d'un producteur
     */
    public function getProducerOffers(Producer $producer, array $filters = [])
    {
        $query = Offer::where('producer_id', $producer->id)
            ->with(['product', 'location']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Obtenir statistiques d'une offre
     */
    public function getStatistics(Offer $offer): array
    {
        return [
            'total_orders' => $offer->orderItems()->count(),
            'total_quantity_sold' => $offer->orderItems()->where('status', 'completed')->sum('quantity'),
            'total_revenue' => $offer->orderItems()->where('status', 'completed')->sum('subtotal'),
            'remaining_quantity' => $offer->remaining_quantity,
            'views_count' => $offer->views_count,
            'conversion_rate' => $offer->views_count > 0 
                ? round(($offer->orderItems()->count() / $offer->views_count) * 100, 2)
                : 0,
        ];
    }
}