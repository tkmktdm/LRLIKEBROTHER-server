<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TopController;

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
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// ********************
// login api
// ********************
// curl -X POST http://localhost/api/login -H "Content-Type: application/json" -d '{"email":"test@example.com", "password":"password"}'

// ********************
// user get api *YOUR_ACCESS_TOKENを変更する
// ********************
// curl -X GET http://localhost/api/user -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
// curl -X GET http://localhost/api/users -H "Authorization: Bearer 2|0iLhsAmJ9LKKdgvjWlC5R3VnuPrwAa3ksUEJb8PN78a15aaa"

Route::post('/register', [RegisterController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::middleware('auth:sanctum')->group(function () {
    // Route::get('/', [TopController::class, 'index'])->name('top');

    // ユーザー
    Route::prefix('setting')->group(function () {
        Route::get('/user', [UserController::class, 'user'])->name('user');
        Route::post('/user', [UserController::class, 'update'])->name('user_update');
    });
    // その他ユーザー
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users');
        Route::get('/{id}', [UserController::class, 'show'])->name('user_show');
    });

    // TokeAI(gemini)
    Route::prefix('aitalk')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('aitalk');
    });

    // logout
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
});
// Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::get('/aa', function () {
    return [70,30];
});