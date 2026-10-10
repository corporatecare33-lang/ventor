<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Product;
use App\Models\Review;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(): View
    {
        $reviews = Review::approved()->with('product:id,name,slug')->latest()->paginate(18);
        $stats = Review::approved()->selectRaw('COUNT(*) as total, COALESCE(AVG(rating),0) as average')->first();

        return view('reviews.index', [
            'reviews' => $reviews,
            'stats' => $stats,
            'seo' => Seo::make('Customer reviews', 'Read verified reviews of LeatherX wallets, NFC smart wallets and bags from customers across Bangladesh.')->canonical(route('reviews.index')),
        ]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:150'],
            'body' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $user = $request->user();

        if (Review::where('product_id', $product->id)->where('user_id', $user->id)->exists()) {
            return back()->withErrors(['body' => 'You have already reviewed this product.'], 'review');
        }

        $order = $user->orders()
            ->where('status', OrderStatus::Delivered)
            ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
            ->latest()->first();

        Review::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'order_id' => $order?->id,
            'name' => $user->name,
            'rating' => $data['rating'],
            'title' => strip_tags((string) ($data['title'] ?? '')) ?: null,
            'body' => strip_tags($data['body']),
            'is_verified_purchase' => (bool) $order,
            'is_approved' => false,
        ]);

        return redirect(route('products.show', $product).'#reviews')->with('success', 'Thank you! Your review will appear after moderation.');
    }
}
