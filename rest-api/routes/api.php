<?php

use App\Http\Controllers\Api\ImageController;
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

Route::prefix('images')->group(function () {
    Route::get('/', [ImageController::class, 'index']);
    Route::post('/', [ImageController::class, 'storeOrUpdate']);
    Route::get('/{id}', [ImageController::class, 'show']);
    Route::delete('/{id}', [ImageController::class, 'destroy']);
});
