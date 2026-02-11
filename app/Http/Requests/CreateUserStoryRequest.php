<?php

namespace App\Http\Requests;

use App\Models\UserStory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUserStoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', [UserStory::class, $this->route('project')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:1000'],
            'story_points' => ['nullable', 'integer'],
            'sprint_id' => [
                'nullable',
                'uuid',
                Rule::exists('sprints', 'id')
                    ->where('project_id', $this->route('project')->id),
            ],
        ];
    }
}
