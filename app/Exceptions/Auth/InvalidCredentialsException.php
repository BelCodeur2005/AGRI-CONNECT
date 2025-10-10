<?php

// app/Exceptions/Auth/InvalidCredentialsException.php
namespace App\Exceptions\Auth;

use App\Exceptions\BaseAgriConnectException;

class InvalidCredentialsException extends BaseAgriConnectException
{
    protected int $statusCode = 401;
    protected string $errorCode = 'INVALID_CREDENTIALS';

    public function __construct()
    {
        parent::__construct('Téléphone ou mot de passe incorrect');
    }
}