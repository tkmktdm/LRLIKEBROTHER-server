<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redis;
use \Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        Log::debug("request--------");
        Log::debug($request);
        // バリデーション
        $validator = Validator::make($request->all(), [
            'name' => 'required | string | max:255',
            'kana' => 'string | max:255',
            'birthday' => 'string | max:255',
            'gender' => 'integer | max:255',
            'email' => 'required | email | max:255 | unique:users,email',
            'password' => 'required | string | min:8',
            // 'name' => ['required'],
            // 'email' => ['required', 'email'],
            // 'password' => ['required']
        ]);

        if ($validator->fails()) {
            Log::debug($validator->errors());
            return response()->json($validator->messages(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // ユーザーを作成
        $user = User::create([
            // 'name' => $request->name,
            // 'email' => $request->email,
            // 'password' => Hash::make($request->password),
            'name'       => $request->name,
            'kana'       => $request->kana,
            'birthday'   => $request->birthday,
            'gender'     => $request->gender,
            'email'      => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        Redis::set($token, json_encode($user));
        return response()
            ->json([
                'message' => 'ログイン成功',
                'user' => $user,
                'token' => $token
            ], 200)
            ->cookie('auth_token', $token, 6000 * 24, null, null, true, true)
            ->cookie('user', $user, null, null, null, true, true);
        // return response()->json([
        //     'message' => 'User registered successfully',
        //     'user' => $user,
        // ], 201);
        // return response()->json('User registration completed', Response::HTTP_OK);
    }
    //
}
