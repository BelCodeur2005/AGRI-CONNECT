<?php

// app/Traits/HasRatings.php
namespace App\Traits;

use App\Models\Rating;

trait HasRatings
{
    /**
     * Relation polymorphique vers ratings
     */
    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    /**
     * Mettre à jour la note moyenne
     */
    public function updateRating(): void
    {
        $ratings = $this->ratings()->get();

        if ($ratings->isEmpty()) {
            $this->update([
                'average_rating' => 0,
                'total_ratings' => 0,
            ]);
            return;
        }

        $this->update([
            'average_rating' => round($ratings->avg('overall_score'), 2),
            'total_ratings' => $ratings->count(),
        ]);
    }

    /**
     * Obtenir distribution des notes
     */
    public function getRatingDistribution(): array
    {
        $distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

        $counts = $this->ratings()
            ->selectRaw('overall_score, COUNT(*) as count')
            ->groupBy('overall_score')
            ->pluck('count', 'overall_score')
            ->toArray();

        return array_merge($distribution, $counts);
    }

    /**
     * Vérifier si bien noté
     */
    public function hasGoodRating(float $threshold = 4.0): bool
    {
        return $this->average_rating >= $threshold && $this->total_ratings >= 5;
    }

    /**
     * Obtenir les meilleures évaluations
     */
    public function topRatings(int $limit = 5)
    {
        return $this->ratings()
            ->where('overall_score', '>=', 4)
            ->where('is_verified', true)
            ->orderBy('overall_score', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}