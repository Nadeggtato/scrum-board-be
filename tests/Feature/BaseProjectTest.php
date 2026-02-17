<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BaseProjectTest extends TestCase
{
    use RefreshDatabase;

    protected User $developer;

    protected User $nonMember;

    protected User $projectManager;

    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(RolePermissionSeeder::class);

        $this->developer = User::factory()->create();
        $this->developer->assignRole(Role::DEVELOPER);

        $this->projectManager = User::factory()->create();
        $this->projectManager->assignRole(Role::PROJECT_MANAGER);

        $this->project = Project::factory()->create(['creator_id' => $this->projectManager->id]);
        $this->project->members()->sync([$this->projectManager->id, $this->developer->id]);

        $this->nonMember = User::factory()->create();
        $this->nonMember->assignRole(Role::PROJECT_MANAGER);
    }
}
