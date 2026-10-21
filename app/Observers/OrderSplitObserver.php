<?php

namespace App\Observers;

use App\Models\OrderSplit;
use App\Models\DesignerStat;

class OrderSplitObserver
{
    public function created(OrderSplit $split): void
    {
        $this->recalcEarning($split->designer_id);
    }

    public function updated(OrderSplit $split): void
    {
        $this->recalcEarning($split->designer_id);
    }

    public function deleted(OrderSplit $split): void
    {
        $this->recalcEarning($split->designer_id);
    }

    private function recalcEarning(string $designerId): void
    {
        $totalEarning = OrderSplit::where('designer_id', $designerId)
                                  ->sum('designer_earning');

        DesignerStat::updateOrCreate(
            ['designer_id' => $designerId],
            [
                'total_earning' => $totalEarning,
            ]
        );
    }
}