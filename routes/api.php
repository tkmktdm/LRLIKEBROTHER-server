<?php

use App\Http\Controllers\AiTalkController;
use App\Http\Controllers\Api\AiAgentController;
use App\Http\Controllers\Api\AiTalkHistoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TopController;
use App\Http\Controllers\Api\McpTaskController;

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\Mcp\GeminiTaskController;
use App\Http\Controllers\Api\TaskController as ApiTaskController;

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
    // curl -X GET http://localhost/api/setting/user -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
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
        // Route::post('/chat', [AiTalkController::class, 'allResponseTalkEndWait'])->name('aitalk_allRes');
        Route::get('/genelate', [AiTalkController::class, 'geminiGenerateTalk'])->name('geminiGenerateTalk');
        // Route::get('/genelate', [AiTalkController::class, 'generateTalk'])->name('aitalk_generate');
        Route::post("/mcp/gemini", [GeminiTaskController::class, "createTask"])->name("mcp_gemini_task");
    });

    // // tasks
    // Route::prefix('tasks')->group(function () {
    //     Route::get('/', [TaskController::class, 'index'])->name('tasks');
    //     // Route::post('/{tasks}', [TaskController::class, 'update'])->name('tasks_update');
    //     Route::post('/', [TaskController::class, 'store'])->name('tasks_store');
    //     Route::post('/{task}', [TaskController::class, 'update'])->name('tasks_update');
    //     Route::delete('/{task}', [TaskController::class, 'destroy'])->name('tasks_delete');
    //     // Route::post('/{task}', [TaskController::class, 'update'])->name('tasks_update');
    // });

    // 主機能
    // カテゴリー
    Route::apiResource('categories', CategoryController::class);
    // タスク
    Route::post('tasks/reorder', [ApiTaskController::class, 'reorder']);
    Route::apiResource('tasks', ApiTaskController::class);
    // AIエージェント
    Route::apiResource('ai_agents', AiAgentController::class);
    // AIトーク履歴
    Route::apiResource('ai_talks', AiTalkHistoryController::class);


    // logout
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
});
// Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// Route::get('/aa', function () {
//     return [70, 30];
// });
// Route::post("/mcp/task", [McpTaskController::class, "createTask"])->name("mcp_task");
Route::get("/mcp/test", [McpTaskController::class, "mcpTest"])->name("mcp_test");
