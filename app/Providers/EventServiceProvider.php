<?php

namespace App\Providers;

use App\Events\ProceedCartEvent;
use App\Listeners\ValidatePriceEvent;
use App\Models\orders;
use App\Models\orders_items;
use App\Models\orders_tracking;
use App\Models\User;
use App\Observers\OrderItemObserver;
use App\Observers\OrderObserver;
use App\Observers\OrderStatusObserver;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        ProceedCartEvent::class => [
            ValidatePriceEvent::class
        ]
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
        //User::observe(UserObserver::class);
        orders_tracking::observe(OrderStatusObserver::class);
        orders::observe(OrderObserver::class);
        orders_items::observe(OrderItemObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
