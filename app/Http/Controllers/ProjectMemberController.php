<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddProjectMemberRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as ResponseCode;

class ProjectMemberController extends Controller
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
     * Display the specified resource.
     */
    public function show(ProjectMember $projectMember)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProjectMember $projectMember)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProjectMember $projectMember)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProjectMember $projectMember)
    {
        //
    }
}
