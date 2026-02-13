<?php

namespace App\Http\Requests;

use App\Models\Sprint;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkAddSprintRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', [Sprint::class, $this->route('project')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d'],
            'name_pattern' => ['required', 'integer', Rule::in(Sprint::NAMING_PATTERNS)],
            'increment_start' => ['required_if:name_pattern,'.Sprint::PATTERN_INCREMENTAL, 'integer'],
            'sprint_duration' => ['required', 'integer', 'min:1', 'max:4'],
        ];
    }
}
