<?php

// app/Traits/HasPriceCalculation.php
namespace App\Traits;

trait HasPriceCalculation
{
    /**
     * Calculer sous-total
     */
    public function calculateSubtotal(float $quantity, float $unitPrice): float
    {
        return round($quantity * $unitPrice, 2);
    }

    /**
     * Calculer commission plateforme
     */
    public function calculatePlatformCommission(float $subtotal, float $rate = null): float
    {
        $commissionRate = $rate ?? (config('agri-connect.platform_commission', 7) / 100);
        return round($subtotal * $commissionRate, 2);
    }

    /**
     * Calculer montant net producteur
     */
    public function calculateProducerNet(float $subtotal, float $commission): float
    {
        return round($subtotal - $commission, 2);
    }

    /**
     * Appliquer réduction
     */
    public function applyDiscount(float $amount, float $discountPercent): float
    {
        return round($amount * (1 - ($discountPercent / 100)), 2);
    }

    /**
     * Calculer TVA (si applicable)
     */
    public function calculateTax(float $amount, float $taxRate): float
    {
        return round($amount * ($taxRate / 100), 2);
    }

    /**
     * Formater prix FCFA
     */
    public function formatPrice(float $price): string
    {
        return number_format($price, 0, ',', ' ') . ' FCFA';
    }
}