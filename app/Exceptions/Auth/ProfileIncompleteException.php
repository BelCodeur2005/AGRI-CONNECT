<?php

// app/Exceptions/Auth/ProfileIncompleteException.php
namespace App\Exceptions\Auth;

use App\Exceptions\BaseAgriConnectException;

class ProfileIncompleteException extends BaseAgriConnectException
{
    protected int $statusCode = 403;
    protected string $errorCode = 'PROFILE_INCOMPLETE';

    public function __construct(array $missingFields = [])
    {
        parent::__construct(
            'Veuillez compléter votre profil',
            ['missing_fields' => $missingFields]
        );
    }
}
