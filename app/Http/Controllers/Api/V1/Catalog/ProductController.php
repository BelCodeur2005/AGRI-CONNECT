<?php

// app/Http/Controllers/Api/V1/Catalog/ProductController.php
namespace App\Http\Controllers\Api\V1\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Resources\Catalog\ProductResource;
use App\Services\Catalog\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $products = $this->productService->getAll($request->all());

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
        ]);
    }

    public function withOffers(): JsonResponse
    {
        $products = $this->productService->getWithActiveOffers();

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
        ]);
    }
}