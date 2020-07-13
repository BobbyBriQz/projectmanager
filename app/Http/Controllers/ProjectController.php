<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProjectController extends Controller
{
    //

    public function create(Request $request){
        $token = JWTAuth::parseToken()->getToken();
        $token_user = JWTAuth::toUser($token);

        $user = User::find($token_user->id);
        if(!is_null($user)){

            $validation = Validator::make($request->all(), [
                'name' => 'required',
                'idea' => 'required',
                'functionality' => 'required',
            ]);

            if($validation->fails()){

                return Response([
                    'status' => false,
                    'message' => 'Project creation failed',
                    'error' => collect(collect($validation->errors())->first())->first()
                ], 400);
            }

            $project = new Project();
            $project->name = $request->name;
            $project->idea = $request->idea;
            $project->functionality = $request->functionality;
            $project->owner_id = $user->id;

            $saveSuccessful = $project->save();

            if($saveSuccessful == 1){
                $project_id = $project->id;
                $user_id = $project->owner_id;

                //Associate user with project on pivot table
                DB::insert('insert into project_user (project_id, user_id) values (?,?)', [$project_id, $user_id]);

                return Response([
                    'status' => true,
                    'message' => 'Project creation successful',
                    'data' => [
                        'project' => $project->load('tasks', 'users')
                    ]
                ], 200);

            }else{

                return Response([
                    'status' => false,
                    'message' => 'Project creation failed',
                    'error' => 'Save not successful',
                ], 400);
            }

        }else{

            return Response([
                'status' => false,
                'message' => 'Project creation failed',
                'error' => 'User does not exist'
            ], 400);
        }
    }

    public function update(Request $request, $id){
        $token = JWTAuth::parseToken()->getToken();
        $token_user = JWTAuth::toUser($token);

        $project = User::find($token_user->id)->projects->where('id', $id)->first();
        if(!is_null($project)) {


            if($request->filled('name')){

                $project->name = $request->input('name');
            }

            if($request->filled('idea')){

                $project->phone_number = $request->input('idea');
            }
            if($request->filled('functionality')){

                $project->email = $request->input('functionality');

            }

            if($request->filled('name') || $request->filled('idea')|| $request->filled('functionality')){

                $updateSuccessful = $project->update();

                if($updateSuccessful == 1) {
                    return response([
                        'status' => true,
                        'message' => 'Project updated successfully',
                        'data' => [
                            'project' => $project->load('tasks', 'users')
                        ]
                    ], 200);
                }else{
                    return Response([
                        'status' => false,
                        'message' => 'Project update failed',
                    ], 400);
                }
            }else{
                return response([
                    'status' => false,
                    'error' => 'No parameter was set'
                ], 401);
            }



        }else{

            return Response([
                'status' => false,
                'message' => 'Project update failed',
                'error' => 'Project does not exist'
            ], 400);
        }
    }


    public function addCollaborator($project_id, $user_id){

        //Get token from request
        $token = JWTAuth::parseToken()->getToken();

        //Get user from token
        $token_user = JWTAuth::toUser($token);

        //get project
        $project = User::find($token_user->id)->projects->where('id', $project_id)->first();

        if(!is_null($project)){

            //Check that user owns project && User is not trying to add himself
            if($project->owner_id == $token_user->id && $token_user->id != $user_id){

                //Check that new user is not already a collaborator
               $result = DB::select('select project_id, user_id from project_user where project_id = ? AND user_id = ?  ', [$project_id, $user_id]);

               //if new user is not already a collaborator
               if(empty($result) ){

                   //insert project id and user id into pivot table
                   $collaboratorIsAdded = DB::insert('insert into project_user (project_id, user_id) values (?,?)', [$project_id, $user_id]);

                   if($collaboratorIsAdded){
                       return response([
                           'status' => true,
                           'message' => 'Collaborator added successfully',
                           'data' => [
                               'project' => $project->load('tasks', 'users')
                           ]
                       ], 200);

                   }else{

                       return Response([
                           'status' => false,
                           'message' => 'Failed to add new collaborator',
                           'error' => 'Pivot table insert failed'
                       ], 400);
                   }
               }else{

                   return Response([
                       'status' => false,
                       'message' => 'Failed to add new collaborator',
                       'error' => 'User is already a collaborator'
                   ], 400);

               }

            }else{
                return Response([
                    'status' => false,
                    'message' => 'Failed to add new collaborator',
                    'error' => 'You do not have authorisation'
                ], 400);
            }
        }else{
            return Response([
                'status' => false,
                'message' => 'Failed to add new collaborator',
                'error' => 'Project does not exist'
            ], 400);
        }
    }

    public function removeCollaborator($project_id, $user_id){

        //Get token from request
        $token = JWTAuth::parseToken()->getToken();

        //Get user from token
        $token_user = JWTAuth::toUser($token);

        //get project
        $project = User::find($token_user->id)->projects->where('id', $project_id)->first();

        if(!is_null($project)){

            //Check that user owns project && User is not trying to remove himself
            if($project->owner_id == $token_user->id && $token_user->id != $user_id){

                //Check that user to be removed is already a collaborator
                $result = DB::select('select project_id, user_id from project_user where project_id = ? AND user_id = ?  ', [$project_id, $user_id]);

                //if user to be removed is already a collaborator
                if(!empty($result) ){

                    //delete one entry of project id and user id from pivot table
                    $collaboratorIsDeleted = DB::delete('delete from project_user where project_id = ? AND user_id = ? limit 1', [$project_id, $user_id]);

                    if($collaboratorIsDeleted){
                        return response([
                            'status' => true,
                            'message' => 'Collaborator removed successfully',
                            'data' => [
                                'project' => $project->load('tasks', 'users')
                            ]
                        ], 200);

                    }else{

                        return Response([
                            'status' => false,
                            'message' => 'Failed to remove collaborator',
                            'error' => 'Pivot table delete failed'
                        ], 400);
                    }
                }else{

                    return Response([
                        'status' => false,
                        'message' => 'Failed to remove collaborator',
                        'error' => 'User is not a collaborator'
                    ], 400);

                }

            }else{
                return Response([
                    'status' => false,
                    'message' => 'Failed to add new collaborator',
                    'error' => 'You do not have authorisation'
                ], 400);
            }
        }else{
            return Response([
                'status' => false,
                'message' => 'Failed to add new collaborator',
                'error' => 'Project does not exist'
            ], 400);
        }
    }
}
