<?php

// app/Services/Catalog/ProductService.php
namespace App\Services\Catalog;

use App\Models\Product;
use App\Models\ProductCategory;

class ProductService
{
    /**
     * Obtenir tous les produits
     */
    public function getAll(array $filters = [])
    {
        $query = Product::active()->with('category');

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Obtenir produits avec offres actives
     */
    public function getWithActiveOffers()
    {
        return Product::active()
            ->whereHas('activeOffers')
            ->withCount('activeOffers')
            ->with('category')
            ->orderBy('name')
            ->get();
    }

    /**
     * Obtenir catégories
     */
    public function getCategories()
    {
        return ProductCategory::active()
            ->withCount('products')
            ->get();
    }

    /**
     * Obtenir produits par catégorie
     */
    public function getByCategory(int $categoryId)
    {
        return Product::active()
            ->where('category_id', $categoryId)
            ->withCount('activeOffers')
            ->orderBy('name')
            ->get();
    }

    /**
     * Obtenir suggestions de produits
     */
    public function getSuggestions(string $query, int $limit = 10)
    {
        return Product::active()
            ->search($query)
            ->limit($limit)
            ->get();
    }
}