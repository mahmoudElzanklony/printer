<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ActivationAccountController;
use App\Http\Controllers\Auth\ForgetPasswordController;
use App\Http\Controllers\CategoriesControllerResource;
use App\Http\Controllers\ServicesControllerResource;
use App\Http\Controllers\PropertiesControllerResource;
use App\Http\Controllers\PropertiesHeadingControllerResource;
use App\Http\Controllers\CouponsControllerResource;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\GeneralServiceController;

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
Route::group(['middleware'=>'changeLang'],function (){
    // auth module
    Route::group(['prefix'=>'/auth'],function (){
        Route::post('/login',[LoginController::class,'login']);
        Route::post('/activate-account',[ActivationAccountController::class,'index']);
        Route::post('/register',[RegisterController::class,'register']);
        Route::post('/forget-password',[ForgetPasswordController::class,'index']);
        Route::post('/new-password',[ForgetPasswordController::class,'new_password']);
    });
    // orders
    Route::group(['prefix'=>'/orders','middleware'=>'auth:sanctum'],function (){
       Route::get('/',[OrdersController::class,'index']);
       Route::post('/create',[OrdersController::class,'create']);
       Route::post('/update-status',[OrdersController::class,'update_status'])->middleware('admin');
       Route::post('/remove-item',[OrdersController::class,'remove_item']);
       Route::post('/cancel',[OrdersController::class,'cancel']);
       Route::post('/validate-coupon',[OrdersController::class,'validate_coupon']);
    });
    // notifications
    Route::group(['prefix'=>'/notifications','middleware'=>'auth:sanctum'],function (){
        Route::get('/',[NotificationsController::class,'index']);
        Route::post('/read-at',[NotificationsController::class,'seen']);
    });
    // resources
    Route::resources([
        'categories'=>CategoriesControllerResource::class,
        'properties-heading'=>PropertiesHeadingControllerResource::class,
        'properties'=>PropertiesControllerResource::class,
        'services'=>ServicesControllerResource::class,
        'coupons'=>CouponsControllerResource::class,
    ]);

    Route::post('/deleteitem',[GeneralServiceController::class,'delete_item']);

});
