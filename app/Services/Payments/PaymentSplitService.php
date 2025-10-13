<?php

// app/Services/Payments/PaymentSplitService.php
namespace App\Services\Payments;

use App\Models\Payment;
use App\Models\PaymentSplit;

class PaymentSplitService
{
    /**
     * Créer les splits pour chaque producteur
     */
    public function createSplits(Payment $payment): void
    {
        $order = $payment->order;

        // Grouper items par producteur
        $itemsByProducer = $order->items->groupBy('producer_id');

        foreach ($itemsByProducer as $producerId => $items) {
            $amount = $items->sum('subtotal');
            $commission = $items->sum('platform_commission');
            $netAmount = $amount - $commission;

            PaymentSplit::create([
                'payment_id' => $payment->id,
                'producer_id' => $producerId,
                'amount' => $amount,
                'platform_commission' => $commission,
                'net_amount' => $netAmount,
                'status' => 'held',
            ]);
        }
    }

    /**
     * Obtenir statistiques des splits
     */
    public function getSplitsSummary(Payment $payment): array
    {
        $splits = $payment->splits;

        return [
            'total_producers' => $splits->count(),
            'total_amount' => $splits->sum('amount'),
            'total_commission' => $splits->sum('platform_commission'),
            'total_net_to_producers' => $splits->sum('net_amount'),
            'splits' => $splits,
        ];
    }
}