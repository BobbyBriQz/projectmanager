<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    //
    public function login(Request $request){

        $validator = Validator::make($request->all(), [

            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'status' => false,
                'message' => 'User login failed',
                'error' => collect(collect($validator->errors())->first())->first()
            ], 401);
        }

        $credentials = $request->only('email', 'password');

        $user = User::where('email', $request->email)->first();

        $token = JWTAuth::fromUser($user, $credentials);

        if(is_null($user)){
            return response([
                'status' => false,
                'message' => 'User does not exist',

            ], 400);
        }

        if(Hash::check($request->password, $user->password)){
            return response([
                'status' => true,
                'message' => 'User login successful',
                'data' => [
                    'user' => $user,
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => Config::get('jwt')['ttl'] * 60
                ]
            ], 200);

        }else{

            return response([
                'status' => false,
                'message' => 'User login failed',
                'error' => 'Wrong password'
            ], 400);
        }

    }

    public function register(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone_number' => 'required|numeric|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'status' => false,
                'message' => 'User registration failed',
                'error' => collect(collect($validator->errors())->first())->first()
            ], 401);
        }

        $user = new User();
        $user->name = $request->input('name');
        $user->phone_number = $request->input('phone_number');
        $user->email = $request->input('email');
        $user->password = bcrypt($request->input('password'));

        $saveSuccessful = $user->save();

        if($saveSuccessful == 1){
            return response([
                'message' => 'User registered successfully',
                'data' => [
                    'user' => $user
                ]
            ], 200);
        }
    }

    public function resetPassword(){

    }
}
