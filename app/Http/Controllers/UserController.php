<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function user(Request $request)
    {
        // 認証済みのユーザーを返す
        return response()->json($request->user());
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = json_encode(User::all());
        return $users;
    }

    // /**
    //  * Store a newly created resource in storage.
    //  */
    // public function store(Request $request)
    // {
    //     //
    // }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // $user = json_encode(User::all()->get($id));
        $user = User::all()->get($id);
        if (!$user) return response()->json(['error' => 'NotFound'], 404);
        return json_encode($user);
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, string $id)
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required | string | max:255',
            'kana' => 'string | max:255',
            'birthday' => 'string | max:255',
            'gender' => 'string | max:255',
            'email' => 'required | email | max:255',
            'password' => 'nullable | string | min:8',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422); // HTTPステータスコード422を返す
        }
        $auth = Auth::user();
        if (!$auth) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        // 更新フィールドを設定
        $auth->fill($request->except('password'));
        // passwordが提供された場合のみハッシュ化して更新
        if ($request->filled('password')) {
            $auth->password = bcrypt($request->password);
        }

        $auth->name = $request->name;
        $auth->kana = $request->kana;
        $auth->birthday = $request->birthday;
        $auth->gender = $request->gender;
        $auth->email = $request->email;
        // return $auth;
        try {
            // return '1';
            $auth->save();
            return response()->json($auth);
        } catch(Error $e) {
            return 'error';
            return response()->json($e);
        }
        // return $auth;
    }
}
