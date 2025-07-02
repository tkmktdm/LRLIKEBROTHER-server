<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use \Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'name' => 'required | string | max:255',
            'kana' => 'string | max:255',
            'birthday' => 'string | max:255',
            'gender' => 'string | max:255',
            'email' => 'required | email | max:255 | unique:users,email',
            'password' => 'required | string | min:8',
            // 'name' => ['required'],
            // 'email' => ['required', 'email'],
            // 'password' => ['required']
        ]);

        if ($validator->fails()) {
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

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
        ], 201);
        // return response()->json('User registration completed', Response::HTTP_OK);
    }
    //
}
