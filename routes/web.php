<?php

use App\Http\Controllers\Employees\CreateController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Employees\IndexController;

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

Route::get('/', function () {
    /*app()->setLocale('ar');
    return view('invoice', [
        'order' => \App\Models\orders::query()->first()->with(['items','payment','user'])->first(),
    ]);*/
});




Route::group(['prefix'=>'/employees'],function (){
   Route::get('/', IndexController::class);
   Route::post('/create/', CreateController::class)->name('employee.create');
});

