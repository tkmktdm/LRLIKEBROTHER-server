<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Redis;

// ********************
// login api
// ********************
// curl -X POST http://localhost/api/login -H "Content-Type: application/json" -d '{"email":"test@example.com", "password":"password"}'

// ********************
// user get api *YOUR_ACCESS_TOKENを変更する
// ********************
// curl -X GET http://localhost/api/user -H "Authorization: Bearer YOUR_ACCESS_TOKEN"

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // RateLimiter::for('login', function (Request $request) {
        //     return Limit::perMinute(5)->by($request->ip());
        // });
        $credentials = $request->validate([
            'email' => 'required | email',
            'password' => 'required|min:8|max:32',
        ], [
            'email.required' => "メッセージを入力してください" ,
            'email.email' => "正しいメールアドレス形式で入力してください" ,
            'password.required' => "メッセージを入力してください" ,
            'password.min' => "パスワードは８文字以上必要です" ,
        ]);
        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = $request->user();
        $token = $user->createToken('auth_token')->plainTextToken;

        // dd($token);

        Redis::set($token, json_encode($user));
    
        return response()
            ->json([
                'message' => 'ログイン成功',
                'user' => $user,
                'token' => $token
            ], 200)
            ->cookie('auth_token', $token, 6000*24, null,null, true, true)
            ->cookie('user', $user, null, null,null, true, true);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
}
