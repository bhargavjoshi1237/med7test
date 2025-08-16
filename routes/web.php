<?php

use App\Http\Controllers\AffiliateController;
use App\Livewire\CheckoutPage;
use App\Livewire\CheckoutSuccessPage;
use App\Livewire\CollectionPage;
use App\Livewire\Home;
use App\Livewire\ProductPage;
use App\Livewire\SearchPage;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Laravel\Cashier\Http\Controllers\WebhookController;
use App\Http\controllers\StripeController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\CartController;
use App\Livewire\AffiliateDashboard;
use App\Livewire\Test23;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', Home::class);

Route::get('/collections/{slug}', CollectionPage::class)->name('collection.view');

Route::get('/products/{slug}', ProductPage::class)->name('product.view');

Route::get('search', SearchPage::class)->name('search.view');

Route::get('checkout', CheckoutPage::class)->name('checkout.view');

Route::get('checkoutnew', App\Livewire\CheckoutNewPage::class)->name('checkoutnew.view');

Route::get('checkout/success', CheckoutSuccessPage::class)->name('checkout-success.view');

Route::get('account', App\Livewire\AccountPage::class)->name('account.view');

Route::get('/api/cart', CartController::class);
Route::post('/api/cart', [CartController::class, 'store']);
Route::get('/cart', App\Livewire\Components\CartView::class)->name('cart.view');

Route::post(
    'stripe/webhook',
    [WebhookController::class, 'handleWebhook']
)->name('cashier.webhook');

// Affiliate Export Routes
Route::get('/admin/affiliate/export-activity', [App\Http\Controllers\AffiliateExportController::class, 'exportActivityReport'])
    ->name('affiliate.export.activity');

Route::get('/admin/affiliate/dashboard', \App\Filament\Pages\AffiliateDashboard::class)
    ->name('filament.lunar.pages.affiliate.dashboard');

    Route::post('/purchase', function (Request $request) {
    $stripeCharge = $request->user()->charge(
        100,
    );
});


Route::get('/payment', [StripeController::class, 'checkout'])->name('payment');
Route::get('/payment/success', [StripeController::class, 'success'])->name('payment.success');
Route::get('/payment/cancel', [StripeController::class, 'cancel'])->name('payment.cancel');
// Route::post('/payment/refund/{paymentIntentId}', [StripeController::class, 'refund'])->name('payment.refunds');
// Add to routes/web.php
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);
Route::post('/payment/refund/{paymentIntentId}', [StripeController::class, 'refund'])->name('payment.refund');

// Add Stripe payment initiation route for checkout page
Route::post('/checkout/stripe', [App\Livewire\CheckoutPage::class, 'initiateStripePayment'])->name('checkout.stripe');

// Test route for new checkout
Route::get('/test-checkout', function () {
    return view('test-checkout');
})->name('test.checkout');

// Authentication routes
Route::get('/login', App\Livewire\AuthPage::class)->name('login');
Route::get('/register', App\Livewire\AuthPage::class)->name('register');
Route::post('/logout', function() {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/');
})->name('logout');

// Affiliate routes
Route::get('/affiliate/dashboard', App\Livewire\AffiliatePortal::class)->name('affiliate.portal');
Route::get('/affiliate/dashboard/main', AffiliateDashboard::class)->name('affiliate.dashboard.main');
