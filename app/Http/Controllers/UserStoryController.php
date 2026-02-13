<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkAddUserStoriesRequest;
use App\Http\Requests\CreateUserStoryRequest;
use App\Http\Requests\UpdateUserStoryRequest;
use App\Http\Resources\UserStoryResource;
use App\Models\Project;
use App\Models\UserStory;
use App\Services\UserStory\BulkAddUserStoriesService;
use Arr;
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

    public function bulkAdd(
        BulkAddUserStoriesRequest $request,
        Project $project,
        BulkAddUserStoriesService $bulkAddUserStoriesService)
    {
        $result = $bulkAddUserStoriesService->execute($project, $request->validated());
        $areAllSaved = Arr::get($result, 'requested_count') === Arr::get($result, 'saved');
        $message = $areAllSaved ? 'User stories successfully added.' : 'Some user stories could not be saved.';

        return Response::json([
            'message' => $message,
            'result' => $result,
        ], $areAllSaved ? ResponseCode::HTTP_CREATED : ResponseCode::HTTP_MULTI_STATUS);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, UserStory $userStory)
    {
        $this->authorize('view', [UserStory::class, $project, $userStory]);
        $userStory = $this->loadIncludes($userStory, request(), UserStory::ALLOWED_INCLUDES);

        return Response::json(new UserStoryResource($userStory), ResponseCode::HTTP_OK);
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
