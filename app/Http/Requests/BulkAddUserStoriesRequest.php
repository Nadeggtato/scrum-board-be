<?php

namespace App\Http\Requests;

use App\Models\UserStory;
use Illuminate\Foundation\Http\FormRequest;

class BulkAddUserStoriesRequest extends FormRequest
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
            'user_stories' => ['required', 'string', 'max:5000'],
        ];
    }
}
