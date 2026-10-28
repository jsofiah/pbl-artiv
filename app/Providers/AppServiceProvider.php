<?php

namespace App\Providers;

use App\Models\Message;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderRefund;
use App\Models\Payment;
use App\Models\Review;
use App\Models\OrderSplit;
use App\Observers\MessageObserver;
use App\Observers\OrderObserver;
use App\Observers\OrderRefundObserver;
use App\Observers\PaymentObserver;
use App\Observers\ReviewObserver;
use App\Observers\OrderSplitObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;

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
        OrderRefund::observe(OrderRefundObserver::class);
        Payment::observe(PaymentObserver::class);
        OrderSplit::observe(OrderSplitObserver::class);
        Review::observe(ReviewObserver::class);

        // Catat waktu login terakhir (dipakai di popup Detail Akun admin)
        Event::listen(Login::class, function (Login $event) {
            $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
        });

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