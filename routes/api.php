<?php

use App\Http\Controllers\AdsControllerResource;
use App\Http\Controllers\Auth\ActivationAccountController;
use App\Http\Controllers\Auth\ForgetPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoriesControllerResource;
use App\Http\Controllers\CitiesControllerResource;
use App\Http\Controllers\ContactsControllerResource;
use App\Http\Controllers\CountriesControllerResource;
use App\Http\Controllers\CouponsControllerResource;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GeneralServiceController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropertiesControllerResource;
use App\Http\Controllers\PropertiesHeadingControllerResource;
use App\Http\Controllers\RatesController;
use App\Http\Controllers\RolesControllerResource;
use App\Http\Controllers\SavedLocationsControllerResource;
use App\Http\Controllers\SavedPropertiesSettingControllerResource;
use App\Http\Controllers\ServicesControllerResource;
use App\Http\Controllers\ShipmentPricesControllerResource;
use App\Http\Controllers\SmsHistoryControllerResource;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\WalletHistoryController;
use App\Http\Controllers\ZohoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'changeLang'], function () {
    // auth module
    Route::group(['prefix' => '/auth'], function () {
        Route::post('/send-verification', [VerificationController::class, 'send_verification']);
        Route::post('/verify', [VerificationController::class, 'verify']);
        Route::post('/login', [LoginController::class, 'login']);
        Route::post('/activate-account', [ActivationAccountController::class, 'index']);
        Route::post('/register', [RegisterController::class, 'register']);
        Route::post('/forget-password', [ForgetPasswordController::class, 'index']);
        Route::post('/new-password', [ForgetPasswordController::class, 'new_password']);
        Route::post('/logout', [LoginController::class, 'logout']);
        Route::post('/me', [LoginController::class, 'get_user_by_token']);
    });
    // orders
    Route::group(['prefix' => '/orders', 'middleware' => 'auth:sanctum'], function () {
        Route::get('/', [OrdersController::class, 'index']);
        Route::post('/create', [OrdersController::class, 'create']);
        Route::post('/update-status', [OrdersController::class, 'update_status'])
            ->middleware('admin');
        Route::post('/remove-item', [OrdersController::class, 'remove_item']);
        Route::post('/cancel', [OrdersController::class, 'cancel']);
        Route::post('/validate-coupon', [OrdersController::class, 'validate_coupon']);
    });
    // cart
    Route::group(['prefix' => '/cart', 'middleware' => 'auth:sanctum'], function () {
        Route::get('/', [CartController::class, 'get_all_data']);
        Route::post('/', [CartController::class, 'store']);
        Route::post('/update-item', [CartController::class, 'update_item']);
        Route::post('/proceed-cart', [CartController::class, 'proceed_cart']);
    });
    // rates
    Route::group(['prefix' => '/rates', 'middleware' => 'auth:sanctum'], function () {
        Route::get('/', [RatesController::class, 'index']);
        Route::post('/create', [RatesController::class, 'create']);

    });
    // notifications
    Route::group(['prefix' => '/notifications', 'middleware' => 'auth:sanctum'], function () {
        Route::get('/', [NotificationsController::class, 'index']);
        Route::post('/read-at', [NotificationsController::class, 'seen']);
    });
    // profile
    Route::group(['prefix' => '/profile', 'middleware' => 'auth:sanctum'], function () {
        Route::post('/update-info', [ProfileController::class, 'update_info']);
        Route::get('/statistics', [ProfileController::class, 'statistics']);
    });
    // zoho
    Route::group(['prefix' => '/zoho'], function () {
        Route::get('/callback', [ZohoController::class, 'callback']);
    });
    // permissions
    Route::group(['prefix' => '/permissions', 'middleware' => 'auth:sanctum'], function () {
        Route::get('/', PermissionsController::class);
    });

    // wallet history
    Route::group(['prefix' => '/wallet', 'middleware' => 'auth:sanctum'], function () {
        Route::get('/history', [WalletHistoryController::class, 'index']);
        Route::post('/charge', [WalletHistoryController::class, 'charge']);
    });

    // admin panel
    Route::group(['prefix' => '/dashboard', 'middleware' => 'auth:sanctum'], function () {
        Route::get('/users', [DashboardController::class, 'users']);
        Route::get('/orders-statistics', [DashboardController::class, 'orders']);
        Route::get('/orders-summary', [DashboardController::class, 'orders_summary']);
        Route::post('/add-money-to-wallet', [DashboardController::class, 'add_money_to_wallet']);
        Route::post('/take-money-from-wallet', [DashboardController::class, 'take_money_from_wallet']);
        Route::post('/update-tax', [DashboardController::class, 'update_tax']);
        Route::get('/get-tax', [DashboardController::class, 'get_tax']);
        Route::post('/add-employee', [DashboardController::class, 'add_employee']);
        // ->middleware('role_or_permission:pi-money-bill|/tax-value|read');
        Route::group(['prefix' => '/notifications-schedule', 'middleware' => 'auth:sanctum'], function () {
            Route::post('/save', [DashboardController::class, 'create_notification_content']);
        });
    });
    // resources
    Route::resources([
        'categories' => CategoriesControllerResource::class,
        'properties-heading' => PropertiesHeadingControllerResource::class,
        'properties' => PropertiesControllerResource::class,
        'services' => ServicesControllerResource::class,
        'coupons' => CouponsControllerResource::class,
        'countries' => CountriesControllerResource::class,
        'cities' => CitiesControllerResource::class,
        'shipment-prices' => ShipmentPricesControllerResource::class,
        'saved-locations' => SavedLocationsControllerResource::class,
        'saved-properties' => SavedPropertiesSettingControllerResource::class,
        'ads' => AdsControllerResource::class,
        'dynamic-sms' => SmsHistoryControllerResource::class,
        'roles' => RolesControllerResource::class,
        'contacts' => ContactsControllerResource::class,

    ]);

    Route::post('/deleteitem', [GeneralServiceController::class, 'delete_item']);

});
