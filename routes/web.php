<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\Shop\SubscriptionController;
use App\Http\Controllers\CreateSuperAdmin;
use App\Http\Controllers\Admin\CheckOnlineUserController;
use App\Http\Controllers\CustomerLoginController;
use App\Http\Controllers\Gateway\PaymentGatewayController;
use App\Http\Controllers\PassportStorageSupportController;
use App\Http\Controllers\Gateway\PayTabs\ProcessController;
use App\Http\Controllers\Gateway\Bkash\ExecutePaymentController;
use App\Http\Controllers\API\ProductLicenseController;
use App\Http\Controllers\Gateway\PayPal\ProcessController as PayPalProcessController;
use App\Http\Controllers\Gateway\Tap\ProcessController as TapProcessController;
use App\Http\Controllers\Gateway\Tabby\ProcessController as TabbyProcessController;
use App\Http\Controllers\Gateway\Tamara\ProcessController as TamaraProcessController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

    Route::controller(CustomerLoginController::class)->group(function () {
        Route::get('/login/test', 'index');
    });
    // Route::controller(CustomerLoginController::class)->group(function () {
    //     Route::post('/login', 'login')->name('customer.login');
    // });

Route::controller(CreateSuperAdmin::class)->middleware('guest')->group(function(){
    Route::get('/create-root', 'index')->name('create.root');
    Route::any('/create-superadmin', 'store')->name('create.superadmin');
});
Route::get('api/download-product-license/{license}', [ProductLicenseController::class, 'downloadLicensePdf'])->name('download.product.license');

// Change language
Route::get('/change-language', function () {
    if (request()->language) {
        App::setLocale(request()->language);
        session()->put('locale', request()->language);
    }

    return back();
})->name('change.language');

// Install Passport and storage routes
Route::controller(PassportStorageSupportController::class)->group(function () {
    Route::get('/install-passport', 'index')->name('passport.install.index');
    Route::get('/seeder-run', 'seederRun')->name('seeder.run.index');
    Route::get('/storage-install', 'storageInstall')->name('storage.install.index');
});

Route::controller(AuthController::class)->group(function () {
    Route::get('/auth/{provider}/callback', 'callback');
    Route::post('/auth/{provider}/callback', 'callback');
});

// Payment gateway routes
Route::controller(PaymentGatewayController::class)->group(function () {
    // payment routes
    Route::get('/order/{payment}/payment', 'payment')->name('order.payment');

    // success and cancel routes for payment
    Route::get('/order/{payment}/payment/success', 'paymentSuccess')->name('order.payment.success');
    Route::get('/order/{payment}/payment/cancel', 'paymentCancel')->name('order.payment.cancel');

    // success and cancel routes for callback (gateways call back with GET or POST)
    Route::match(['get', 'post'], '/payment/{payment}/callback-success', 'success')->name('payment.success');
    Route::match(['get', 'post'], '/payment/{payment}/callback-cancel', 'cancel')->name('payment.cancel');
});

//Paypal Payment success
Route::get('/paypal/{paypalPayment}/success', [PayPalProcessController::class, 'success'])->name('paypal.payment.success');

// Bkash Payment execute
Route::get('/bkash-payment/{payment}/execute', [ExecutePaymentController::class, 'index'])->name('bkash.payment.execute');

// Paytabs payment execute
Route::match(['get', 'post'], '/paytabs/{payment}/callback', [ProcessController::class, 'callback'])->name('paytabs.payment.callback');
Route::match(['get', 'post'], '/subscription/payment/{payment}/success', [SubscriptionController::class, 'paymentSuccess'])->name('subscription.payment.success');
Route::match(['get', 'post'], '/subscription/payment/{payment}/cancel', [SubscriptionController::class, 'paymentCancel'])->name('subscription.payment.cancel');

Route::match(['get', 'post'], '/tap/payment/callback/{payment}', [TapProcessController::class, 'callback'])->name('tap.payment.execute');

// Tabby: customer redirect back from Tabby, and Tabby's server notifications
Route::get('/tabby/payment/callback/{payment}', [TabbyProcessController::class, 'callback'])->name('tabby.payment.callback');
Route::post('/tabby/webhook', [TabbyProcessController::class, 'webhook'])->name('tabby.webhook');

// Tamara: customer redirect back from Tamara, and Tamara's server notifications
Route::get('/tamara/payment/callback/{payment}', [TamaraProcessController::class, 'callback'])->name('tamara.payment.callback');
Route::post('/tamara/webhook', [TamaraProcessController::class, 'webhook'])->name('tamara.webhook');

// check user is online or not
Route::post('/update/last/seen', [CheckOnlineUserController::class, 'checkOnlineStatus']);

// handle frontend page load
Route::get('/{any}', function () {

    // manage admin and shop routes
    if (request()->is('admin/*', 'shop/*')) {
        return abort(404);
    }

    return view('app');
})->where('any', '.*');
