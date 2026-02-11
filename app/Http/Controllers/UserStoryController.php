<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserStoryRequest;
use App\Http\Resources\UserStoryResource;
use App\Models\Project;
use App\Models\UserStory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as ResponseCode;

class UserStoryController extends ApiController
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
    public function store(CreateUserStoryRequest $request, Project $project)
    {
        $userStory = UserStory::create([...$request->validated(), 'project_id' => $project->id]);

        return Response::json(new UserStoryResource($userStory), ResponseCode::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(UserStory $userStory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserStory $userStory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserStory $userStory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserStory $userStory)
    {
        //
    }
}
