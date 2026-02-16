<?php

namespace App\Http\Requests;

use App\Models\Task;
use App\Rules\IsValidTaskRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', [Task::class, $this->route('project')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $project = $this->route('project');
        $projectId = $project->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'status' => ['required', 'string', new IsValidTaskRule($project)],
            'user_id' => [
                'nullable',
                'uuid',
                'exists:users',
                Rule::exists('project_members', 'user_id')
                    ->where('project_id', $projectId)
                    ->whereNull('deleted_at'),
            ],
            'user_story_id' => [
                'required',
                'uuid',
                Rule::exists('user_stories', 'id')
                    ->where('project_id', $projectId)
                    ->whereNull('deleted_at'),
            ],
        ];
    }
}
