<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserStoryRequest;
use App\Http\Requests\UpdateUserStoryRequest;
use App\Http\Resources\UserStoryResource;
use App\Models\Project;
use App\Models\UserStory;
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
     * Update the specified resource in storage.
     */
    public function update(UpdateUserStoryRequest $request, Project $project, UserStory $userStory)
    {
        $userStory->update([...$request->validated(), 'project_id' => $project->id]);

        return Response::json(
            new UserStoryResource($userStory->refresh()),
            ResponseCode::HTTP_CREATED
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, UserStory $userStory)
    {
        $this->authorize('delete', [UserStory::class, $project, $userStory]);

        $userStory->delete();

        return Response::json(new UserStoryResource($userStory), ResponseCode::HTTP_OK);
    }
}
