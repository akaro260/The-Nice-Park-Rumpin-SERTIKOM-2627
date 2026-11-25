<?php

use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Admin\SchoolApplicationController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'index'])->name('home');
Route::get('/produk/{ticket}', [CatalogController::class, 'show'])->name('catalog.show');
Route::post('/produk/{ticket}/booking', [BookingController::class, 'store'])
    ->middleware(['auth', 'customer', 'throttle:promo-redemption'])
    ->name('bookings.store');

Route::post('/midtrans/notifications', [PaymentController::class, 'notification'])
    ->name('midtrans.notifications');

Route::middleware('auth')->get('/dashboard', function (Request $request) {
    if ($request->user()->role === 'admin') {
        return to_route('admin.products.index');
    }

    $registrations = $request->user()
        ->registrations()
        ->with(['ticket', 'payments' => fn ($query) => $query->latest()])
        ->latest()
        ->paginate(10);

    return view('dashboard', compact('registrations'));
})->name('dashboard');

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('products', ProductController::class)->except('show');
        Route::get('/promo-codes', [PromoCodeController::class, 'index'])
            ->name('promo-codes.index');
        Route::post('/promo-codes', [PromoCodeController::class, 'store'])
            ->name('promo-codes.store');
        Route::patch('/promo-codes/{promoCode}', [PromoCodeController::class, 'update'])
            ->name('promo-codes.update');
        Route::delete('/promo-codes/{promoCode}', [PromoCodeController::class, 'destroy'])
            ->name('promo-codes.destroy');
        Route::get('/applications', [SchoolApplicationController::class, 'index'])
            ->name('applications.index');
        Route::patch('/applications/{registration}/approve', [SchoolApplicationController::class, 'approve'])
            ->name('applications.approve');
        Route::patch('/applications/{registration}/reject', [SchoolApplicationController::class, 'reject'])
            ->name('applications.reject');
    });

Route::middleware(['auth', 'customer'])->group(function () {
    Route::post('/pesanan/{registration}/checkout', [PaymentController::class, 'checkout'])
        ->middleware('throttle:promo-redemption')
        ->name('payments.checkout');
    Route::post('/pesanan/{registration}/status', [PaymentController::class, 'checkStatus'])
        ->name('payments.status');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
