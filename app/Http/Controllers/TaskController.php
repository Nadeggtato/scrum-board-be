<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\ProjectConfiguration;
use App\Models\Task;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as ResponseCode;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateTaskRequest $request, Project $project)
    {
        $task = Task::create([
            ...$request->validated(),
            'status' => ProjectConfiguration::STATUS_TO_DO,
        ]);

        return Response::json(new TaskResource($task), ResponseCode::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Project $project, Task $task)
    {
        $task->update($request->validated());

        return Response::json(new TaskResource($task->refresh()), ResponseCode::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        //
    }
}
