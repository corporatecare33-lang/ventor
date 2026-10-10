<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    /** Printable invoice for the order owner or staff with order access. */
    public function __invoke(Request $request, Order $order): View
    {
        $user = $request->user();

        abort_unless(
            $user && ($order->user_id === $user->id || $user->can('view orders')),
            403
        );

        return view('orders.invoice', ['order' => $order->load(['items', 'paymentMethodModel'])]);
    }
}
