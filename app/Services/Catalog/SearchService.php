<?php

// app/Services/Catalog/SearchService.php
namespace App\Services\Catalog;

use App\Models\Offer;
use App\Models\Product;
use App\Models\Producer;

class SearchService
{
    /**
     * Rechercher des offres
     */
    public function searchOffers(string $query, array $filters = [])
    {
        $offerQuery = Offer::active()
            ->with(['product', 'producer.user', 'location']);

        // Recherche texte
        if (!empty($query)) {
            $offerQuery->search($query);
        }

        // Filtres
        if (isset($filters['category_id'])) {
            $offerQuery->whereHas('product', function($q) use ($filters) {
                $q->where('category_id', $filters['category_id']);
            });
        }

        if (isset($filters['location_id'])) {
            $offerQuery->where('location_id', $filters['location_id']);
        }

        if (isset($filters['min_price'])) {
            $offerQuery->where('price_per_unit', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $offerQuery->where('price_per_unit', '<=', $filters['max_price']);
        }

        if (isset($filters['organic']) && $filters['organic']) {
            $offerQuery->organic();
        }

        if (isset($filters['min_quantity'])) {
            $offerQuery->where('quantity_available', '>=', $filters['min_quantity']);
        }

        // Tri
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        match($sortBy) {
            'price_asc' => $offerQuery->orderBy('price_per_unit', 'asc'),
            'price_desc' => $offerQuery->orderBy('price_per_unit', 'desc'),
            'quantity' => $offerQuery->orderBy('quantity_available', 'desc'),
            'rating' => $offerQuery->join('producers', 'offers.producer_id', '=', 'producers.id')
                                   ->orderBy('producers.average_rating', 'desc'),
            default => $offerQuery->orderBy($sortBy, $sortOrder),
        };

        return $offerQuery->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Recherche globale (offres + produits + producteurs)
     */
    public function globalSearch(string $query): array
    {
        return [
            'offers' => Offer::active()
                ->search($query)
                ->with(['product', 'producer.user'])
                ->limit(5)
                ->get(),
            
            'products' => Product::active()
                ->search($query)
                ->withCount('activeOffers')
                ->limit(5)
                ->get(),
            
            'producers' => Producer::verified()
                ->whereHas('user', function($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%");
                })
                ->with('user')
                ->limit(5)
                ->get(),
        ];
    }

    /**
     * Filtres disponibles
     */
    public function getAvailableFilters(): array
    {
        return [
            'categories' => \App\Models\ProductCategory::active()->get(),
            'locations' => \App\Models\Location::where('is_active', true)->get(),
            'price_range' => [
                'min' => Offer::active()->min('price_per_unit'),
                'max' => Offer::active()->max('price_per_unit'),
            ],
        ];
    }
}