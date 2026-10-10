<?php

use App\Models\Cart;
use Illuminate\Support\Facades\Schedule;

/*
| Shared hosting (cPanel) has no long-running queue worker, so the scheduler
| drains the queue every minute. Add ONE cron job in cPanel:
|   * * * * * cd /home/USER/leatherx && php artisan schedule:run >> /dev/null 2>&1
*/
Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=3')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Remove abandoned guest carts older than 30 days.
Schedule::call(fn () => Cart::whereNull('user_id')->whereNull('converted_at')->where('updated_at', '<', now()->subDays(30))->delete())
    ->daily()
    ->name('prune-abandoned-carts');

Schedule::command('queue:prune-failed --hours=168')->daily();
