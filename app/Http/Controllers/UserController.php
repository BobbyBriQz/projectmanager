<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    //

    public function users(){

        $users = User::all();
        return response([
            'status' => 'true',
            'message' => 'Users retrieved successfully',
            'data' => [
                'user' => $users
            ]
        ], 200);
    }



    public function updateUserInfo(Request $request, $id){

        $user = User::find($id);

        if (!is_null($user)){

            $validator = Validator::make(request()->all(), [
                'name' => 'sometimes',
                'phone_number' => 'numeric',
                'email' => 'email',
            ]);

            if($validator->fails()){

                return response([
                    'message' => 'User update failed',
                    'error' => collect(collect($validator->errors())->first())->first()
                ], 401);
            }


            if($request->filled('name')){

                $user->name = $request->input('name');
            }

            if($request->filled('phone_number')){

                $user->phone_number = $request->input('phone_number');
            }
            if($request->filled('email')){

                $user->email = $request->input('email');

            }

            if($request->filled('old_password')){

                if($request->filled('new_password')){

                    if(Hash::check($request->old_password, $user->password)){

                        if(strlen($request->new_password) >= 5){

                            $user->password = bcrypt($request->input('new_password'));
                        }else{
                            return response([
                                'status' => false,
                                'message' => 'User update failed',
                                'error' => "New password must be at least 6 characters"
                            ], 401);
                        }

                    }else{
                        return response([
                            'status' => false,
                            'message' => 'User update failed',
                            'error' => "Wrong old password"
                        ], 401);
                    }


                }else{
                    return response([
                        'status' => false,
                        'message' => 'User update failed',
                        'error' => "please provide both old and new password"
                    ], 401);
                }

            }


            if($request->filled('password')
                || $request->filled('email')
                || $request->filled('phone_number')
                || $request->filled('name')
                || ($request->filled('old_password') && $request->filled('new_password'))
            )  {

                $updateSuccessful = $user->update();

                if($updateSuccessful == 1){
                    return response([
                        'status' => true,
                        'message' => 'User updated successfully',
                        'data' => [
                            'user' => $user
                        ]
                    ], 200);
                }
            }else{
                return response([
                    'status' => false,
                    'error' => 'No parameter was set'
                ], 401);
            }
        }else{

            return response([
                'status' => false,
                'error' => 'User does not exist',
            ]);
        }
    }

    public function getUser($id){

        $user = User::find($id);

        if (!is_null($user)){
            return response([
                'status' => true,
                'message' => 'User retrieved successfully',
                'data' => [
                    'user' => $user,
                ]
            ]);
        }else{
            return response([
                'status' => false,
                'error' => 'User does not exist',
            ]);
        }
    }

    public function deleteUser(Request $request, $id){

        $user = User::find($id);
        $password = $request->input('password');

        $validator = Validator::make($request->all(), [
            'password' => 'required',
        ]);

        if($validator->fails()){

            return response([
                'status' => false,
                'error' => collect(collect($validator->errors())->first())->first(),
            ], 400);
        }

        if (!is_null($user)){

            if(Hash::check($password, $user->password)){

                $user->delete();

                return response([
                    'status' => true,
                    'message' => 'User deleted successfully',
                    'data' => [
                        'deleted_user' => $user,
                    ]
                ]);
            }else{
                return response([
                    'status' => false,
                    'error' => 'Provide correct password to delete user',
                ]);
            }

        }else{
            return response([
                'status' => false,
                'error' => 'User does not exist',
            ]);
        }
    }

    public function deletedUsers(){
        $users = User::onlyTrashed()->get();
        return response([
            'status' => true,
            'message' => 'Displayed deleted users',
            'data' => [
                'deleted_users' => $users,
            ]
        ]);
    }

    public function recoverDeletedUsers(){
        $users = User::onlyTrashed()->get();
        User::onlyTrashed()->restore();

        return response([
            'status' => true,
            'message' => 'Restored deleted users',
            'data' => [
                'restored_users' => $users,
            ]
        ]);
    }
}
