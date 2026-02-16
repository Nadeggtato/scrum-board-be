<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\SprintController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserStoryController;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware(['api', 'auth:sanctum'])
                ->prefix('api')
                ->group(function () {
                    Route::prefix('projects')
                        ->name('projects.')
                        ->controller(ProjectController::class)
                        ->group(base_path('routes/api/projects.php'));

                    Route::prefix('projects/{project}')
                        ->group(function () {
                            Route::prefix('sprints')
                                ->name('sprints.')
                                ->controller(SprintController::class)
                                ->group(base_path('routes/api/sprints.php'));

                            Route::prefix('user-stories')
                                ->name('user_stories.')
                                ->controller(UserStoryController::class)
                                ->group(base_path('routes/api/user_stories.php'));

                            Route::prefix('tasks')
                                ->name('tasks.')
                                ->controller(TaskController::class)
                                ->group(base_path('routes/api/tasks.php'));

                            Route::prefix('members')
                                ->name('members.')
                                ->controller(ProjectMemberController::class)
                                ->group(base_path('routes/api/project_members.php'));
                        });
                });

        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
