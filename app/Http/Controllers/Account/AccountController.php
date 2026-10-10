<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user = $request->user();

        return view('account.dashboard', [
            'recentOrders' => $user->orders()->withCount('items')->latest()->limit(5)->get(),
            'stats' => [
                'orders' => $user->orders()->count(),
                'nfc' => $user->nfcProfiles()->count(),
                'warranty' => $user->warrantyRegistrations()->count(),
                'addresses' => $user->addresses()->count(),
            ],
            'seo' => Seo::make('My account')->noindex(),
        ]);
    }

    public function orders(Request $request): View
    {
        return view('account.orders', [
            'orders' => $request->user()->orders()->withCount('items')->latest()->paginate(10),
            'seo' => Seo::make('My orders')->noindex(),
        ]);
    }

    public function order(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        $order->load(['items.product:id,slug', 'statusHistories', 'engravingRequests', 'paymentMethodModel', 'payments']);

        return view('account.order', ['order' => $order, 'seo' => Seo::make('Order '.$order->order_number)->noindex()]);
    }

    public function warranty(Request $request): View
    {
        return view('account.warranty', [
            'records' => $request->user()->warrantyRegistrations()->latest()->get(),
            'seo' => Seo::make('My warranty')->noindex(),
        ]);
    }

    public function editProfile(Request $request): View
    {
        return view('account.profile', ['user' => $request->user(), 'seo' => Seo::make('Profile')->noindex()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        $request->merge(['phone' => normalize_phone($request->input('phone'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['required', 'regex:/^01[3-9]\d{8}$/'],
        ]);

        $user->update([...$data, 'name' => strip_tags($data['name'])]);

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $request->user()->update(['password' => $data['password']]);

        return back()->with('success', 'Password changed.');
    }
}
