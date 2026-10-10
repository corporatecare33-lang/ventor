<?php

namespace App\Providers;

use App\Models\User;
use App\Services\CartService;
use App\View\Composers\LayoutComposer;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(CartService::class);
    }

    public function boot(): void
    {
        // Catch N+1 queries and silently-dropped attributes during development.
        Model::preventLazyLoading(! $this->app->isProduction());
        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());
        Model::automaticallyEagerLoadRelationships();

        Paginator::defaultView('components.pagination');

        // Super admins pass every permission check.
        Gate::before(fn (User $user) => $user->hasRole('super_admin') ? true : null);

        Event::listen(Login::class, function (Login $event) {
            if ($event->user instanceof User) {
                $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
                app(CartService::class)->mergeGuestCart(request()->cookie(CartService::COOKIE), $event->user->id);
            }
        });

        RateLimiter::for('forms', fn (Request $request) => Limit::perMinute(6)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('checkout', fn (Request $request) => Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('auth', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        View::composer(['layouts.app', 'errors.404', 'errors::404'], LayoutComposer::class);
    }
}
