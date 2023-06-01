<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/product/downloadImages',[\App\Http\Controllers\Api\V1\ProductController::class, 'downloadImages']);
Route::get('/product/updateImagesUrl',[\App\Http\Controllers\Api\V1\ProductController::class, 'updateImagesUrl']);
