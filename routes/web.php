<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APIController;
use App\Http\Controllers\DHLController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\NewOrderController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PasswordSecurityController;
use App\Http\Controllers\TestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Route::get('/api/ups/invoice/{id}', 'APIController@getInvoiceUPS');
// Route::get('/api/ups/label/{id}', 'APIController@getLabelUPS');

Route::get('/token/ups/invoice/{id}', [APIController::class, 'getInvoiceUPS']);
Route::get('/token/ups/cod/{id}', [APIController::class, 'getCOD']);
Route::get('/api/ups/gif/{id}', [APIController::class, 'getLabelGifUPS']);
Route::get('/pharmacies/logo/{id}', [PharmacyController::class, 'getLogo']);

Route::post('/', [NewOrderController::class, 'newOrder']);
Auth::routes();

if (App::environment(['local', 'staging'])) {
    Route::get('dtest', function () {
        return 'Test';
    });
    Route::get('test-azure', [TestController::class, 'testAzure']);
    Route::post('upload-file', [TestController::class, 'upload']);
};


//2fa routes
Route::get('/2fa', [PasswordSecurityController::class, 'show2faForm']);
Route::post('/generate2faSecret', [PasswordSecurityController::class, 'generate2faSecret'])
    ->name('generate2faSecret');
Route::post('/2fa', [PasswordSecurityController::class, 'enable2fa'])
    ->name('enable2fa');
Route::post('/disable2fa', [PasswordSecurityController::class, 'disable2fa'])
    ->name('disable2fa');

Route::group(['middleware' => ['2fa']], function () {
    Route::post('/verify-2fa', [PasswordSecurityController::class, 'twoFaVerify']);
});

Route::group(['middleware' => ['auth']], function () {
    Route::get('/logout', [LoginController::class, 'logout']);

    Route::group(['middleware' => ['2fa']], function () {
        Route::get('/', [HomeController::class, 'index']);
    });

    Route::get('/statistics', [HomeController::class, 'statistics']);
    Route::get('/countries', [HomeController::class, 'countries']);
    Route::get('/delivery-companies', [HomeController::class, 'deliveryCompanies']);
    Route::get('/products', [HomeController::class, 'products']);
    Route::get('/clients', [HomeController::class, 'clients']);
    Route::get('/doctors', [HomeController::class, 'doctors']);

    Route::get('/reset-order/{id}', [OrderController::class, 'reset']);
    Route::get('/order/{id}', [OrderController::class, 'details']);
    Route::get('/order/{id}/check-document', [OrderController::class, 'checkDocument']);
    Route::get('/order/{id}/download-document', [OrderController::class, 'downloadDocument']);
    Route::get('/order/{id}/activity', [OrderController::class, 'getActivity']);
    Route::post('/resend-authorization', [OrderController::class, 'checkAuthorizationCode']);
    Route::get('/delivery-companies', [OrderController::class, 'deliveryCompanies']);
    Route::post('/import-tracking', [OrderController::class, 'importTracking']);
    Route::post('/log-reprint', [OrderController::class, 'logReprint']);
    Route::get('/orders/search', [OrderController::class, 'search']);
    Route::get('/orders', [OrderController::class, 'orders']);
    Route::delete('/orders/{id}', [OrderController::class, 'cancelOrder']);
    Route::get('/reports/hourly', [OrderController::class, 'hourly']);
    Route::get('/reports/csv', [OrderController::class, 'csv']);
    //UPS routes
    Route::get('/api/ups/shipment-validation/{id}', [APIController::class, 'shipmentValidationUPS']);
    Route::get('/api/ups/label/{id}', [APIController::class, 'getLabelUPS']);
    // Route::get('/api/ups/invoice/{id}', [APIController::class, 'getInvoiceUPS']);
    Route::get('/api/ups/manual/{id}', [APIController::class, 'UPSmanual']);
    //DHL routes
    Route::get('/api/dhl/shipment-validation/{id}', [APIController::class, 'shipmentValidationDHL']);
    Route::get('/api/dhl/label/{id}', [APIController::class, 'getLabelDHL']);
    Route::get('/api/dhl/manual/{id}', [APIController::class, 'DHLmanual']);
    Route::post('/api/dhl/{id}/resend-pdf', [APIController::class, 'DHLResendPDF']);

    //TNT routes
    Route::get('/api/tnt/manual/{id}', [APIController::class, 'TNTmanual']);
    //RM routes
    Route::get('/api/rmail/manual/{id}', [APIController::class, 'RMmanual']);
    //DPD routes
    Route::get('/api/dpd/manual/{id}', [APIController::class, 'DPDmanual']);
    Route::get('/api/dpd/shipment-validation/{id}', [APIController::class, 'shipmentValidationDPD']);
    Route::get('/api/dpd/label/{id}', [APIController::class, 'getLabelDPD']);
    //Order editing
    Route::get('/order-edit/{id}', [OrderController::class, 'editAddress']);
    Route::post('/order-edit/{id}', [OrderController::class, 'updateAddress']);
    Route::post('/order-edit/check/{id}', [OrderController::class, 'checkUpdateDetail']);

    //users
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}/authorizable', [UserController::class, 'authorizable']);
    Route::post('/users/{id}/authorizable', [UserController::class, 'toggleAuthorizable']);
    Route::get('/users/{id}', [UserController::class, 'user']);
    Route::get('/esa_login_status', [UserController::class, 'loggedToEsa']);
    Route::post('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'delete']);
    Route::put('/users', [UserController::class, 'create']);
    Route::get('/login_as/{id}', [UserController::class, 'loginAs']);

    Route::get('/users/{id}/2fa-status', [PasswordSecurityController::class, 'twoFactorVerifyStatus']);
    Route::get('/users/{id}/2fa-code', [PasswordSecurityController::class, 'code']);
    Route::post('/users/{id}/2fa-enable', [PasswordSecurityController::class, 'enable2fa']);
    Route::post('/users/{id}/2fa-disable', [PasswordSecurityController::class, 'disable2fa']);

    //pharmacies
    Route::get('/pharmacies', [PharmacyController::class, 'index']);
    Route::get('/pharmacies/list', [PharmacyController::class, 'list']);
    Route::get('/pharmacies/{id}/banlist', [PharmacyController::class, 'banlist']);
    Route::get('/pharmacies/{id}', [PharmacyController::class, 'pharmacy']);
    Route::put('/pharmacies', [PharmacyController::class, 'create']);
    Route::post('/pharmacies/logo/{id}', [PharmacyController::class, 'logo']);
    Route::post('/pharmacies/{id}', [PharmacyController::class, 'update']);
    Route::delete('/pharmacies/{id}', [PharmacyController::class, 'delete']);

    //TEST
    Route::get('/test/dhl/shipment-validation/{id}', [DHLController::class, 'shipmentValidationDHL']);
    Route::get('/test/dhl/book-pickup-global/{id}', [DHLController::class, 'BookPickupGlobal']);
    Route::get('/test/dhl/capability-eu/{id}', [DHLController::class, 'CapabilityEU']);
    Route::get('/test/dhl/book-pickup-eu/{id}', [DHLController::class, 'BookPickupEU']);
    Route::get('/test/dhl/tracking/{id}', [DHLController::class, 'Tracking']);
    Route::get('/test/dhl/tracking-awb/{id}', [DHLController::class, 'TrackingAWB']);
    Route::get('/test/dhl/test', [DHLController::class, 'test']);
    Route::post('/blank-response', [APIController::class, 'blank']);

    //Client
    Route::get('/clients/index', [ClientController::class, 'index']);
    Route::delete('/clients/{id}', [ClientController::class, 'deactivate']);
    Route::get('/clients/{id}', [ClientController::class, 'client']);
    Route::patch('/clients/{id}', [ClientController::class, 'update']);
    Route::post('/clients', [ClientController::class, 'insert']);

    /*HELP*/
    Route::get('/info', [HelpController::class, 'info']); // index of help entries
});
