<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth;
use App\Http\Controllers\Api\V1\Profile;
use App\Http\Controllers\Api\V1\Catalog;
use App\Http\Controllers\Api\V1\Cart;
use App\Http\Controllers\Api\V1\Orders;
use App\Http\Controllers\Api\V1\Payments;
use App\Http\Controllers\Api\V1\Deliveries;
use App\Http\Controllers\Api\V1\Ratings;
use App\Http\Controllers\Api\V1\Notifications;
use App\Http\Controllers\Api\V1\Favorites;
use App\Http\Controllers\Api\V1\Disputes;

/*
|--------------------------------------------------------------------------
| API Routes - Agri-Connect v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ==========================================
    // ROUTES PUBLIQUES (sans authentification)
    // ==========================================
    
    // Health Check
    Route::get('/health', function () {
        return response()->json([
            'success' => true,
            'data' => [
                'status' => 'healthy',
                'timestamp' => now()->toIso8601String(),
                'version' => '1.0.0',
            ],
        ]);
    });

    // ============ AUTH ============
    Route::prefix('auth')->group(function () {
        Route::post('/register', Auth\RegisterController::class);
        Route::post('/login', Auth\LoginController::class);
        Route::post('/verify-phone', [Auth\VerificationController::class, 'verify']);
        Route::post('/resend-code', [Auth\VerificationController::class, 'resend']);
        Route::post('/forgot-password', [Auth\PasswordController::class, 'forgot']);
        Route::post('/reset-password', [Auth\PasswordController::class, 'reset']);
    });

    // ============ CATALOG PUBLIC ============
    Route::prefix('catalog')->group(function () {
        Route::get('/categories', [Catalog\CategoryController::class, 'index']);
        Route::get('/products', [Catalog\ProductController::class, 'index']);
        Route::get('/products/with-offers', [Catalog\ProductController::class, 'withOffers']);
        
        Route::get('/offers', Catalog\OfferBrowseController::class);
        Route::get('/offers/{offer}', [Catalog\OfferController::class, 'show']);
        
        Route::get('/search', [Catalog\SearchController::class, 'search']);
        Route::get('/search/global', [Catalog\SearchController::class, 'global']);
        Route::get('/search/filters', [Catalog\SearchController::class, 'filters']);
    });

    // ============ WEBHOOKS ============
    Route::prefix('webhooks')->group(function () {
        Route::post('/payments/orange-money', [Payments\PaymentWebhookController::class, 'orangeMoney']);
        Route::post('/payments/mtn-momo', [Payments\PaymentWebhookController::class, 'mtnMomo']);
    });

    // ==========================================
    // ROUTES PROTÉGÉES (authentification requise)
    // ==========================================
    
    Route::middleware(['auth:sanctum'])->group(function () {

        // ============ AUTH (authenticated) ============
        Route::prefix('auth')->group(function () {
            Route::post('/logout', Auth\LogoutController::class);
            Route::get('/check-verification', [Auth\VerificationController::class, 'check']);
            Route::post('/change-password', [Auth\PasswordController::class, 'change']);
        });

        // ============ PROFILE ============
        Route::prefix('profile')->middleware(['verified.phone'])->group(function () {
            Route::get('/', [Profile\ProfileController::class, 'show']);
            Route::put('/', [Profile\ProfileController::class, 'update']);
            Route::post('/complete', [Profile\ProfileController::class, 'complete']);
            Route::delete('/', [Profile\ProfileController::class, 'destroy']);

            // Producer Profile
            Route::prefix('producer')->middleware(['role:producer'])->group(function () {
                Route::get('/', [Profile\ProducerProfileController::class, 'show']);
                Route::put('/', [Profile\ProducerProfileController::class, 'update']);
            });

            // Buyer Profile
            Route::prefix('buyer')->middleware(['role:buyer'])->group(function () {
                Route::get('/', [Profile\BuyerProfileController::class, 'show']);
                Route::put('/', [Profile\BuyerProfileController::class, 'update']);
            });

            // Transporter Profile
            Route::prefix('transporter')->middleware(['role:transporter'])->group(function () {
                Route::get('/', [Profile\TransporterProfileController::class, 'show']);
                Route::put('/', [Profile\TransporterProfileController::class, 'update']);
                Route::post('/toggle-availability', [Profile\TransporterProfileController::class, 'toggleAvailability']);
            });
        });

        // ============ NOTIFICATIONS ============
        Route::prefix('notifications')->middleware(['verified.phone'])->group(function () {
            Route::get('/', [Notifications\NotificationController::class, 'index']);
            Route::post('/{notification}/read', [Notifications\NotificationController::class, 'markAsRead']);
            Route::post('/read-all', [Notifications\NotificationController::class, 'markAllAsRead']);
            Route::delete('/{notification}', [Notifications\NotificationController::class, 'destroy']);
        });

        // ============ PRODUCER ROUTES ============
        Route::middleware(['role:producer', 'verified.phone', 'profile.complete'])->group(function () {
            
            // Offers Management
            Route::prefix('producer')->group(function () {
                Route::apiResource('offers', Catalog\OfferController::class)->except(['index', 'show']);
                Route::get('/offers', [Catalog\OfferController::class, 'index']);
                Route::get('/offers/{offer}/statistics', [Catalog\OfferController::class, 'statistics']);
                
                // Orders
                Route::get('/orders', [Orders\ProducerOrderController::class, 'index']);
                Route::get('/orders/pending', [Orders\ProducerOrderController::class, 'pending']);
                
                // Order Items Management
                Route::post('/order-items/{orderItem}/confirm', [Orders\OrderItemController::class, 'confirm']);
                Route::post('/order-items/{orderItem}/reject', [Orders\OrderItemController::class, 'reject']);
                Route::post('/order-items/{orderItem}/ready', [Orders\OrderItemController::class, 'markReady']);
            });
        });

        // ============ BUYER ROUTES ============
        Route::middleware(['role:buyer', 'verified.phone', 'profile.complete'])->group(function () {
            
            // Cart
            Route::prefix('cart')->group(function () {
                Route::get('/', [Cart\CartController::class, 'index']);
                Route::post('/', [Cart\CartController::class, 'store']);
                Route::put('/{cartItem}', [Cart\CartController::class, 'update']);
                Route::delete('/{cartItem}', [Cart\CartController::class, 'destroy']);
                Route::delete('/', [Cart\CartController::class, 'clear']);
                Route::get('/validate', [Cart\CartController::class, 'validate']);
                Route::post('/checkout', [Cart\CartController::class, 'checkout']);
            });

            // Favorites
            Route::prefix('favorites')->group(function () {
                Route::get('/', [Favorites\FavoriteController::class, 'index']);
                Route::post('/', [Favorites\FavoriteController::class, 'store']);
                Route::delete('/offers/{offer}', [Favorites\FavoriteController::class, 'destroy']);
            });

            // Orders (Buyer view)
            Route::prefix('buyer')->group(function () {
                Route::get('/orders', [Orders\BuyerOrderController::class, 'index']);
            });
        });

        // ============ TRANSPORTER ROUTES ============
        Route::middleware(['role:transporter', 'verified.phone', 'profile.complete'])->group(function () {
            
            Route::prefix('transporter')->group(function () {
                Route::get('/deliveries', [Deliveries\TransporterDeliveryController::class, 'index']);
                Route::get('/deliveries/available', [Deliveries\TransporterDeliveryController::class, 'available']);
                Route::get('/deliveries/active', [Deliveries\TransporterDeliveryController::class, 'active']);
                
                Route::get('/delivery-groups', [Deliveries\DeliveryGroupController::class, 'index']);
                Route::get('/delivery-groups/available', [Deliveries\DeliveryGroupController::class, 'available']);
            });
        });

        // ============ SHARED ROUTES (multiple roles) ============
        Route::middleware(['verified.phone', 'profile.complete'])->group(function () {
            
            // Orders (common endpoints)
            Route::prefix('orders')->group(function () {
                Route::get('/', [Orders\OrderController::class, 'index']);
                Route::post('/', [Orders\OrderController::class, 'store'])->middleware(['role:buyer']);
                Route::get('/{order}', [Orders\OrderController::class, 'show']);
                Route::post('/{order}/cancel', [Orders\OrderController::class, 'cancel']);
                Route::post('/{order}/confirm', [Orders\OrderController::class, 'confirm'])->middleware(['role:buyer']);
            });

            // Payments
            Route::prefix('payments')->group(function () {
                Route::post('/orders/{order}/initiate', [Payments\PaymentController::class, 'initiate']);
                Route::get('/{payment}/status', [Payments\PaymentController::class, 'status']);
                Route::post('/orders/{order}/release', [Payments\PaymentController::class, 'release']);
                
                // Payment History
                Route::get('/history/buyer', [Payments\PaymentHistoryController::class, 'buyer'])->middleware(['role:buyer']);
                Route::get('/history/producer', [Payments\PaymentHistoryController::class, 'producer'])->middleware(['role:producer']);
            });

            // Deliveries
            Route::prefix('deliveries')->group(function () {
                Route::get('/{delivery}', [Deliveries\DeliveryController::class, 'show']);
                Route::post('/{delivery}/start', [Deliveries\DeliveryController::class, 'start']);
                Route::post('/{delivery}/update-location', [Deliveries\DeliveryController::class, 'updateLocation']);
                Route::post('/{delivery}/complete', [Deliveries\DeliveryController::class, 'complete']);
                
                // Tracking (accessible par acheteur et transporteur)
                Route::get('/{delivery}/tracking', [Deliveries\DeliveryTrackingController::class, 'show']);
            });

            // Ratings
            Route::prefix('ratings')->group(function () {
                Route::get('/', [Ratings\RatingController::class, 'index']);
                Route::get('/received', [Ratings\RatingController::class, 'received']);
                Route::post('/orders/{order}', [Ratings\RatingController::class, 'store']);
                Route::put('/{rating}', [Ratings\RatingController::class, 'update']);
            });

            // Disputes
            Route::prefix('disputes')->group(function () {
                Route::get('/', [Disputes\DisputeController::class, 'index']);
                Route::post('/orders/{order}', [Disputes\DisputeController::class, 'store']);
                Route::get('/{dispute}', [Disputes\DisputeController::class, 'show']);
            });
        });
    });
});