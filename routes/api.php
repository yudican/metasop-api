<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Master\BannerController;
use App\Http\Controllers\Master\LevelPriceController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Setting\MenuController;
use App\Http\Controllers\Setting\RoleController;
use App\Models\Banner;
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

Route::post('auth/login', [LoginController::class, 'login']);
Route::post('auth/register', [RegisterController::class, 'register']);
Route::middleware('auth:sanctum')->group(function () {
    // profile
    Route::prefix('user')->group(function () {
        Route::get('profile', [ProfileController::class, 'getProfile']);
        Route::get('menu', [ProfileController::class, 'getMenuList']);
    });

    // setting
    Route::prefix('setting')->group(function () {
        // role route resource
        Route::apiResource('role', RoleController::class);
        // menu route resource
        Route::apiResource('menu', MenuController::class);
        Route::post('menu/role/{menu_id}', [MenuController::class, 'updateMenuRole']);
    });

    // product
    Route::prefix('product')->group(function () {
        // load product
        Route::post('sync', [ProductController::class, 'syncProduct']);
        Route::post('list', [ProductController::class, 'getListProduct']);
        Route::get('detail/{product_id}', [ProductController::class, 'getProductDetail']);
        Route::post('update/{product_id}', [ProductController::class, 'updateProduct']);
        Route::post('update/status/{product_id}', [ProductController::class, 'updateStatusProduct']);
    });

    // master
    Route::prefix('master')->group(function () {
        // master

        #banner
        Route::post('banner/list', [BannerController::class, 'list']);
        Route::post('banner/status/{banner_id}', [BannerController::class, 'updateStatus']);
        Route::apiResource('banner', BannerController::class);

        #level price
        Route::post('level-price/list', [LevelPriceController::class, 'list']);
        Route::apiResource('level-price', LevelPriceController::class);
    });
});
