<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
});

// Product API routes
Route::get('/products', [ProductController::class, 'index']);

// Cart API routes
Route::prefix('cart')->name('api.cart.')->group(function () {
    Route::get('/', [CartController::class, 'show'])->name('show');
    Route::post('/', [CartController::class, 'create'])->name('create');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::put('/update', [CartController::class, 'update'])->name('update');
    Route::delete('/remove', [CartController::class, 'remove'])->name('remove');
    Route::delete('/clear', [CartController::class, 'clear'])->name('clear');
});

// Checkout API routes
Route::prefix('checkout')->name('api.checkout.')->group(function () {
    Route::get('/countries', [CheckoutController::class, 'countries'])->name('countries');
    Route::get('/shipping-options', [CheckoutController::class, 'shippingOptions'])->name('shipping-options');
    Route::get('/summary', [CheckoutController::class, 'summary'])->name('summary');
    Route::post('/shipping-address', [CheckoutController::class, 'setShippingAddress'])->name('shipping-address');
    Route::post('/billing-address', [CheckoutController::class, 'setBillingAddress'])->name('billing-address');
    Route::post('/shipping-option', [CheckoutController::class, 'setShippingOption'])->name('shipping-option');
    Route::post('/process', [CheckoutController::class, 'processCheckout'])->name('process');
});

// Order API routes
Route::prefix('orders')->name('api.orders.')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('index');
    Route::get('/{orderId}', [OrderController::class, 'show'])->name('show');
});
