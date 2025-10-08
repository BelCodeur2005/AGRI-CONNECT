<?php
// app/Enums/PaymentMethod.php
namespace App\Enums;

enum PaymentMethod: string
{
    case ORANGE_MONEY = 'orange_money';
    case MTN_MOMO = 'mtn_momo';
    case CASH = 'cash';             // Pour phase pilote
    case BANK_TRANSFER = 'bank_transfer';

    public function label(): string
    {
        return match($this) {
            self::ORANGE_MONEY => 'Orange Money',
            self::MTN_MOMO => 'MTN Mobile Money',
            self::CASH => 'Espèces',
            self::BANK_TRANSFER => 'Virement bancaire',
        };
    }
}

