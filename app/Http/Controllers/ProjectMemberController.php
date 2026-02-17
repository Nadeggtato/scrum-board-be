<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddProjectMemberRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as ResponseCode;

class ProjectMemberController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(AddProjectMemberRequest $request, Project $project)
    {
        $project->members()->syncWithoutDetaching($request->validated('user_ids'));
        $project->load('members');

        return Response::json(new ProjectResource(
            $project->refresh()),
            ResponseCode::HTTP_CREATED
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, User $user)
    {
        $this->authorize('delete', [ProjectMember::class, $project]);
        $member = ProjectMember::firstWhere(['project_id' => $project->id, 'user_id' => $user->id]);

        if (empty($member)) {
            $project->load('members');

            return Response::json([
                'error' => 'Member not found',
                new ProjectResource($project),
            ], ResponseCode::HTTP_UNPROCESSABLE_ENTITY);
        }

        $member->delete();

        return Response::json($member, ResponseCode::HTTP_OK);
    }
}
