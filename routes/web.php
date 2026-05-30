<?php

use App\Http\Controllers\Public\BookingController;
use App\Http\Controllers\Public\ExcursionController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PaymentResultController;
use App\Http\Controllers\Public\SeoController;
use App\Modules\Payments\Http\Controllers\FakePaymentController;
use App\Modules\Payments\Http\Controllers\TBankNotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');

Route::get('/', HomeController::class)->name('home');
Route::get('/excursions/search', [ExcursionController::class, 'search'])->name('excursions.search');
Route::get('/excursions/{excursion:slug}', [ExcursionController::class, 'show'])->name('excursions.show');
Route::get('/slots/{slot}/checkout', [BookingController::class, 'create'])->name('bookings.create');
Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
Route::get('/bookings/{booking:uuid}', [BookingController::class, 'show'])->name('bookings.show');

Route::get('/payment/success', [PaymentResultController::class, 'success'])->name('payment.success');
Route::get('/payment/fail', [PaymentResultController::class, 'fail'])->name('payment.fail');

Route::get('/payments/fake/{payment}/paid', [FakePaymentController::class, 'paid'])->name('payments.fake.paid');
Route::post('/payments/tbank/notifications', TBankNotificationController::class)->name('payments.tbank.notifications');
