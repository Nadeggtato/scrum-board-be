<?php

namespace App\Http\Requests;

use App\Models\Sprint;
use App\Rules\SprintLengthRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class UpdateSprintRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', [
            Sprint::class,
            $this->route('project'),
            $this->route('sprint'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'start' => ['required', 'date_format:Y-m-d'],
            'end' => [
                'required',
                'date_format:Y-m-d',
                'after:start',
                new SprintLengthRule(Carbon::parse($this->input('start'))),
            ],
        ];
    }
}
