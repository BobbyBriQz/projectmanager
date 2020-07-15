<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class TaskController extends Controller
{
    //

    public function create(Request $request, $project_id){

        $validation = Validator::make($request->all(), [
            'description' => 'required',
        ]);

        if($validation->fails()){

            return Response([
                'status' => false,
                'message' => 'Task creation failed',
                'error' => collect(collect($validation->errors())->first())->first()
            ], 400);
        }

        $token = JWTAuth::parseToken()->getToken();
        $token_user = JWTAuth::toUser($token);

        $user = User::find($token_user->id);

        if(!is_null($user)){
            $project = $user->projects->where('id', $project_id)->first();

            if(!is_null($project)){

                $task = new Task();

                $task->description = $request->description;
                $task->is_completed = false;
                $task->project_id = $request->project_id;

                $saveSuccessful = $task->save();

                if($saveSuccessful == 1){

                    return Response([
                        'status' => true,
                        'message' => 'Task creation successful',
                        'data' => [
                            'task' => $task
                        ]
                    ], 200);

                }else{

                    return Response([
                        'status' => false,
                        'message' => 'Task creation failed',
                        'error' => 'Save not successful',
                    ], 400);
                }

            }else{

                return Response([
                    'status' => false,
                    'message' => 'Task creation failed',
                    'error' => 'Project does not exist'
                ], 400);
            }


        }else{

            return Response([
                'status' => false,
                'message' => 'Task creation failed',
                'error' => 'User does not exist'
            ], 400);
        }
    }

    public function changeCompletedStatus($project_id, $task_id){

        $token = JWTAuth::parseToken()->getToken();
        $token_user = JWTAuth::toUser($token);

        $user = User::find($token_user->id);

        if(!is_null($user)){
            $project = $user->projects->where('id', $project_id)->first();

            if(!is_null($project)){

                $task = $project->tasks->where('id', $task_id)->first();
                if(!is_null($task)) {

                    $newStatus = !$task->is_completed;
                    $task->is_completed = $newStatus;

                    $updateSuccessful = $task->update();

                    if ($updateSuccessful == 1) {

                        return Response([
                            'status' => true,
                            'message' => 'Task update successful',
                            'data' => [
                                'task' => $task
                            ]
                        ], 200);

                    } else {

                        return Response([
                            'status' => false,
                            'message' => 'Task update failed',
                            'error' => 'Update not successful',
                        ], 400);
                    }

                }else {

                    return Response([
                        'status' => false,
                        'message' => 'Task update failed',
                        'error' => 'Task does not exist',
                    ], 400);
                }

            }else{

                return Response([
                    'status' => false,
                    'message' => 'Task update failed',
                    'error' => 'Project does not exist'
                ], 400);
            }

        }else{

            return Response([
                'status' => false,
                'message' => 'Task update failed',
                'error' => 'User does not exist'
            ], 400);
        }
    }

    public function delete($project_id, $task_id){

        $token = JWTAuth::parseToken()->getToken();
        $token_user = JWTAuth::toUser($token);

        $user = User::find($token_user->id);

        if(!is_null($user)){
            $project = $user->projects->where('id', $project_id)->first();

            if(!is_null($project)){

                $task = $project->tasks->where('id', $task_id)->first();
                if(!is_null($task)) {



                    $deleteSuccessful = $task->delete();

                    if ($deleteSuccessful == 1) {

                        return Response([
                            'status' => true,
                            'message' => 'Task deleted successfully',
                            'data' => [
                                'deleted_task' => $task
                            ]
                        ], 200);

                    } else {

                        return Response([
                            'status' => false,
                            'message' => 'Task delete failed',
                            'error' => 'Delete not successful',
                        ], 400);
                    }

                }else {

                    return Response([
                        'status' => false,
                        'message' => 'Task delete failed',
                        'error' => 'Task does not exist',
                    ], 400);
                }

            }else{

                return Response([
                    'status' => false,
                    'message' => 'Task delete failed',
                    'error' => 'Project does not exist'
                ], 400);
            }

        }else{

            return Response([
                'status' => false,
                'message' => 'Task delete failed',
                'error' => 'User does not exist'
            ], 400);
        }
    }

    public function restore($project_id, $task_id){

        $token = JWTAuth::parseToken()->getToken();
        $token_user = JWTAuth::toUser($token);

        $user = User::find($token_user->id);

        if(!is_null($user)){
            $project = $user->projects->where('id', $project_id)->first();

            if(!is_null($project)){

                $task = Task::onlyTrashed()->where('project_id', $project_id)->where('id', $task_id)->first();
                if(!is_null($task)) {

                    $task->restore();

                    return Response([
                            'status' => true,
                            'message' => 'Task restored successfully',
                            'data' => [
                                'restored_task' => $task
                            ]
                        ], 200);

                }else {

                    return Response([
                        'status' => false,
                        'message' => 'Task recovery failed',
                        'error' => 'Task does not exist',
                    ], 400);
                }

            }else{

                return Response([
                    'status' => false,
                    'message' => 'Task recovery failed',
                    'error' => 'Project does not exist'
                ], 400);
            }

        }else{

            return Response([
                'status' => false,
                'message' => 'Task recovery failed',
                'error' => 'User does not exist'
            ], 400);
        }
    }

    public function restoreAllInProject($project_id){

        $token = JWTAuth::parseToken()->getToken();
        $token_user = JWTAuth::toUser($token);

        $user = User::find($token_user->id);

        if(!is_null($user)){
            $project = $user->projects->where('id', $project_id)->first();

            if(!is_null($project)){

                $tasks = Task::onlyTrashed()->where('project_id', $project_id)->get();
                if(!is_null($tasks)) {

                    Task::onlyTrashed()->where('project_id', $project_id)->restore();

                    return Response([
                        'status' => true,
                        'message' => 'Task restored successfully',
                        'data' => [
                            'restored_tasks' => $tasks
                        ]
                    ], 200);

                }else {

                    return Response([
                        'status' => false,
                        'message' => 'Task recovery failed',
                        'error' => 'Tasks do not exist',
                    ], 400);
                }

            }else{

                return Response([
                    'status' => false,
                    'message' => 'Tasks recovery failed',
                    'error' => 'Project does not exist'
                ], 400);
            }

        }else{

            return Response([
                'status' => false,
                'message' => 'Tasks recovery failed',
                'error' => 'User does not exist'
            ], 400);
        }
    }

}
