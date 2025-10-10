<?php

// app/Exceptions/Auth/AccountSuspendedException.php
namespace App\Exceptions\Auth;

use App\Exceptions\BaseAgriConnectException;

class AccountSuspendedException extends BaseAgriConnectException
{
    protected int $statusCode = 403;
    protected string $errorCode = 'ACCOUNT_SUSPENDED';

    public function __construct(string $reason = null)
    {
        parent::__construct(
            'Votre compte a été suspendu. Contactez le support.',
            ['reason' => $reason]
        );
    }
}