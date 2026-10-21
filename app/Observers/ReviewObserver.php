<?php

namespace App\Observers;

use App\Models\Review;
use App\Models\DesignerStat;
use App\Models\Order;
use App\Models\OrderSplit;

class ReviewObserver
{
    public function created(Review $review): void
    {
        $this->updateRating($review->designer_id);
    }

    public function updated(Review $review): void
    {
        $this->updateRating($review->designer_id);

        // Kalau designer_id berubah (jarang, tapi untuk safety)
        if ($review->wasChanged('designer_id')) {
            $this->updateRating($review->getOriginal('designer_id'));
        }
    }

    public function deleted(Review $review): void
    {
        $this->updateRating($review->designer_id);
    }

    private function updateRating(?string $designerId): void
    {
        if (!$designerId) return;

        $avgRating = Review::where('designer_id', $designerId)->avg('rating') ?? 0;

        DesignerStat::updateOrCreate(
            ['designer_id' => $designerId],
            [
                'average_rating' => round((float) $avgRating, 2),
            ]
        );
    }
}