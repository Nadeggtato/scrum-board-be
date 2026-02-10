<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSprintRequest;
use App\Http\Requests\UpdateSprintRequest;
use App\Http\Resources\SprintResource;
use App\Models\Project;
use App\Models\Sprint;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as ResponseCode;

class SprintController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateSprintRequest $request, Project $project)
    {
        $sprint = Sprint::create([...$request->validated(), 'project_id' => $project->id]);

        return Response::json(new SprintResource($sprint), ResponseCode::HTTP_OK);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Sprint $sprint)
    {
        $sprint = $this->loadIncludes($sprint, request(), Sprint::ALLOWED_INCLUDES);

        return Response::json(new SprintResource($sprint), ResponseCode::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSprintRequest $request, Project $project, Sprint $sprint)
    {
        $sprint->update($request->validated());

        return Response::json(new SprintResource($sprint->refresh()), ResponseCode::HTTP_OK);
    }
}
