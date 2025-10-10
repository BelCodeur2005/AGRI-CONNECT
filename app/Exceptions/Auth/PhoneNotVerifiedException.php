<?php

// app/Exceptions/Auth/PhoneNotVerifiedException.php
namespace App\Exceptions\Auth;

use App\Exceptions\BaseAgriConnectException;

class PhoneNotVerifiedException extends BaseAgriConnectException
{
    protected int $statusCode = 403;
    protected string $errorCode = 'PHONE_NOT_VERIFIED';

    public function __construct()
    {
        parent::__construct(
            'Veuillez vérifier votre numéro de téléphone avant de continuer',
            ['action_required' => 'phone_verification']
        );
    }
}
