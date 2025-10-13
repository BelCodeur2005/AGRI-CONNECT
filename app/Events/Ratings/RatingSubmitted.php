<?php

// app/Events/Ratings/RatingSubmitted.php
namespace App\Events\Ratings;

use App\Models\Rating;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RatingSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(public Rating $rating) {}
}
