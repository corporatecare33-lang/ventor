<?php

use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Account\AddressController;
use App\Http\Controllers\Account\NfcProfileController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CorporateController;
use App\Http\Controllers\EngravingController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\TapController;
use App\Http\Controllers\WarrantyController;
use Illuminate\Support\Facades\Route;

// Catalogue
Route::get('/', HomeController::class)->name('home');
Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/category/{category:slug}', [ShopController::class, 'category'])->name('categories.show');
Route::get('/product/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
Route::post('/product/{product:slug}/reviews', [ReviewController::class, 'store'])->middleware(['auth', 'throttle:forms'])->name('reviews.store');

// LeatherX Tap (NFC)
Route::get('/leatherx-tap', [TapController::class, 'landing'])->name('tap');
Route::get('/tap/{profile:slug}', [TapController::class, 'show'])->name('nfc.show');
Route::get('/tap/{profile:slug}/contact.vcf', [TapController::class, 'vcard'])->name('nfc.vcard');
Route::get('/tap/{profile:slug}/qr.svg', [TapController::class, 'qr'])->name('nfc.qr');

// Cart & checkout
Route::get('/cart', [CartController::class, 'cart'])->name('cart');
Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
Route::get('/order/{order:order_number}/thank-you', [OrderController::class, 'confirmation'])->middleware('signed')->name('orders.confirmation');
Route::get('/track-order', [OrderController::class, 'trackForm'])->name('orders.track');
Route::post('/track-order', [OrderController::class, 'track'])->middleware('throttle:forms')->name('orders.track.lookup');
Route::get('/order/{order:order_number}/invoice', InvoiceController::class)->name('orders.invoice');

// Services
Route::get('/engraving', [EngravingController::class, 'create'])->name('engraving');
Route::post('/engraving', [EngravingController::class, 'store'])->middleware('throttle:forms')->name('engraving.store');
Route::get('/warranty', [WarrantyController::class, 'create'])->name('warranty');
Route::get('/corporate', [CorporateController::class, 'create'])->name('corporate');
Route::post('/corporate', [CorporateController::class, 'store'])->middleware('throttle:forms')->name('corporate.store');
Route::get('/contact', [ContactController::class, 'create'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:forms')->name('contact.store');

// Content
Route::get('/faq', FaqController::class)->name('faq');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/page/{page:slug}', [PageController::class, 'show'])->name('pages.show');
foreach (['about' => 'about', 'return-policy' => 'return-policy', 'privacy-policy' => 'privacy-policy', 'terms-and-conditions' => 'terms', 'delivery-policy' => 'delivery-policy'] as $slug => $name) {
    Route::get("/{$slug}", [PageController::class, 'fixed'])->defaults('slug', $slug)->name("pages.{$name}");
}

// SEO
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

// Customer authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth');
    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'email'])->middleware('throttle:auth')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->middleware('throttle:auth')->name('password.update');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Customer account
Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
    Route::get('/orders/{order:order_number}', [AccountController::class, 'order'])->name('orders.show');
    Route::get('/profile', [AccountController::class, 'editProfile'])->name('profile');
    Route::put('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::put('/password', [AccountController::class, 'updatePassword'])->name('password.update');
    Route::get('/warranty', [AccountController::class, 'warranty'])->name('warranty');
    Route::resource('addresses', AddressController::class)->except(['show']);
    Route::resource('nfc', NfcProfileController::class)->parameters(['nfc' => 'profile'])->except(['show']);
});
