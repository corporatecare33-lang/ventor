<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /** Reached only through the signed URL issued right after checkout. */
    public function confirmation(Order $order): View
    {
        $order->load(['items', 'paymentMethodModel', 'engravingRequests']);

        return view('orders.confirmation', ['order' => $order, 'seo' => Seo::make('Order confirmed')->noindex()]);
    }

    public function trackForm(): View
    {
        return view('orders.track', ['order' => null, 'seo' => Seo::make('Track your order', 'Check the delivery status of your LeatherX order.')]);
    }

    public function track(Request $request): View
    {
        $data = $request->validate([
            'order_number' => ['required', 'string', 'max:30'],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $order = Order::with(['items', 'statusHistories'])
            ->where('order_number', strtoupper(trim($data['order_number'])))
            ->where('phone', normalize_phone($data['phone']))
            ->first();

        return view('orders.track', [
            'order' => $order,
            'notFound' => ! $order,
            'seo' => Seo::make('Track your order')->noindex(),
        ]);
    }
}
