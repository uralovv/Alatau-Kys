<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Mail\ConfirmationMail;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\SecureCodeController;
use App\Http\Controllers\API\CategoriesController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CartController;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix'=>'secure-code'],function (){
   Route::post('create',[SecureCodeController::class,'create']);
   Route::post('register',[SecureCodeController::class,'register']);
});

Route::group(['prefix'=>'auth'], function() {
    Route::post('login',[AuthController::class,'login']);
//    Route::post('register',[AuthController::class,'register']);
});

Route::group(['prefix'=>'categories'], function (){
   Route::get('/',[CategoriesController::class,'index']);
   Route::get('{id}/products',[CategoriesController::class,'view']);
});
Route::group(['prefix'=>'products'], function (){
    Route::get('search',[ProductController::class,'search']);
    Route::get('{id}',[ProductController::class,'view']);
});

Route::apiResource('cart',CartController::class)->except(['update','index']);



