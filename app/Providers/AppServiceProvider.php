<?php

namespace App\Providers;

use App\Models\Message;
use App\Models\Notification;
use App\Models\Order;
use App\Observers\MessageObserver;
use App\Observers\OrderObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Message::observe(MessageObserver::class);
        Order::observe(OrderObserver::class);

        View::composer('partials.notification-dropdown', function ($view) {
            $user = auth()->user();
            if (!$user) {
                return;
            }

            $items = Notification::where('user_id', $user->id)
                ->latest()
                ->limit(20)
                ->get()
                ->groupBy(fn ($n) => $n->created_at->isToday() ? 'today'
                    : ($n->created_at->isYesterday() ? 'yesterday' : 'older'));

            $view->with([
                'notifItems'  => $items,
                'notifUnread' => Notification::where('user_id', $user->id)->unread()->count(),
            ]);
        });
    }
}