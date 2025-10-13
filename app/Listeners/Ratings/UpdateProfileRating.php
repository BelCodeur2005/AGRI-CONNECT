<?php

// app/Listeners/Ratings/UpdateProfileRating.php
namespace App\Listeners\Ratings;

use App\Events\Ratings\RatingSubmitted;

class UpdateProfileRating
{
    public function handle(RatingSubmitted $event): void
    {
        // Note moyenne déjà mise à jour via Observer dans Rating model
        // Ce listener peut servir pour actions additionnelles
    }
}