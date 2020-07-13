<?php

use App\Models\Project;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tymon\JWTAuth\Facades\JWTAuth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/', function (){
    return response([
        'data' => 'Project manager home'
    ], 200);
});

Route::post('/register', 'AuthController@register');
Route::post('/login', 'AuthController@login');
Route::get('/users', 'UserController@users');

Route::group(['middleware' => ['auth.pm']], function (){
    Route::get('/projects', function(){

        $token = JWTAuth::parseToken()->getToken();
        $user = JWTAuth::toUser($token);

          $projects = User::find($user->id)->projects;

          return $projects->load('tasks', 'users');

    });

    Route::post('/project/create', 'ProjectController@create');
    Route::patch('/project/{id}/update', 'ProjectController@update');
    Route::get('/project/{project_id}/addCollaborator/{user_id}', 'ProjectController@addCollaborator');
    Route::delete('/project/{project_id}/removeCollaborator/{user_id}', 'ProjectController@removeCollaborator');


    Route::get('/user/{id}', 'UserController@getUser');
    Route::patch('/user/{id}', 'UserController@updateUserInfo');
    Route::delete('/user/{id}', 'UserController@deleteUser');
    Route::get('/users/deleted', 'UserController@deletedUsers');
    Route::get('/users/recoverDeleted', 'UserController@recoverDeletedUsers');
});


Route::fallBack(function (){
    return Response([
        'status' => false,
        'message' => 'Endpoint does not exist'
    ], 400);
});
