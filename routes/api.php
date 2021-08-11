<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Mail\ConfirmationMail;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\SecureCodeController;
use App\Http\Controllers\API\CategoriesController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\WishlistController;
use App\Http\Controllers\API\PasswordController;
use App\Http\Controllers\API\CartProductController;
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

//Route::group(['prefix'=>'secure-code'],function (){
//   Route::post('create',[SecureCodeController::class,'create']);
//   Route::post('register',[SecureCodeController::class,'register']);
//});
//Route::group(['prefix'=>'auth'], function() {
//    Route::post('login',[AuthController::class,'login'])->name('login');
//    Route::post('register',[AuthController::class,'register']);
//});

Route::group(['prefix' => 'auth'], function (){
    Route::post('registration',[AuthController::class, 'registration']);
    Route::post('login',[AuthController::class,'login'])->name('login');
    Route::post('email-confirmation',[AuthController::class,'confirm_code']);
});
Route::group(['prefix'=>'categories'], function (){
   Route::get('/',[CategoriesController::class,'index']);
   Route::get('{id}/products',[CategoriesController::class,'view']);
});
Route::group(['prefix'=>'products'], function (){
    Route::get('search',[ProductController::class,'search']);
    Route::get('{id}',[ProductController::class,'view']);
});

//Route::apiResource('cart',CartController::class)->except(['update','index']);
Route::group(['prefix'=>'cart'], function (){
    Route::post('create',[CartController::class,'store']);
    Route::get('{key}', [CartController::class,'show']);
    Route::post('{key}',[CartController::class,'addProducts']);
    Route::delete('{key}',[CartController::class,'removeProduct']);
});
Route::group(['prefix'=>'remastered-cart'], function (){
   Route::post('add',[CartProductController::class,'store']);
   Route::delete('delete',[CartProductController::class,'delete']);
   Route::get('view',[CartProductController::class,'view']);
});

Route::group(['prefix' => 'wishlist'], function (){
   Route::post('add',[WishlistController::class,'store'])->middleware('auth:api');
   Route::delete('delete',[WishlistController::class,'delete'])->middleware('auth:api');
   Route::get('view',[WishlistController::class,'view'])->middleware('auth:api');
});
Route::group(['prefix' => 'profile'], function () {
    Route::patch('password-change',[PasswordController::class,'update'])->middleware('auth:api');
});
Route::post('forgot-password',[PasswordController::class,'forgot']);
Route::post('reset-password',[PasswordController::class,'reset']);



