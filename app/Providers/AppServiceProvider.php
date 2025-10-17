<?php

// app/Providers/AppServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enregistrement des Policies
        Gate::policy(\App\Models\Offer::class, \App\Policies\OfferPolicy::class);
        Gate::policy(\App\Models\Order::class, \App\Policies\OrderPolicy::class);
        Gate::policy(\App\Models\OrderItem::class, \App\Policies\OrderItemPolicy::class);
        Gate::policy(\App\Models\Payment::class, \App\Policies\PaymentPolicy::class);
        Gate::policy(\App\Models\Delivery::class, \App\Policies\DeliveryPolicy::class);
        Gate::policy(\App\Models\Rating::class, \App\Policies\RatingPolicy::class);
        Gate::policy(\App\Models\Dispute::class, \App\Policies\DisputePolicy::class);
    }
}