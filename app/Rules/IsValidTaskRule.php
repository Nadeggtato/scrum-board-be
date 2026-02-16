<?php

namespace App\Rules;

use App\Models\Project;
use App\Models\ProjectConfiguration;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsValidTaskRule implements ValidationRule
{
    private $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $statusConfiguration = $this->project
            ->configurations()
            ->where('type', '=', ProjectConfiguration::TYPE_TASK_STATUSES)
            ->first();
        $statusList = json_decode($statusConfiguration->value, true);

        if (empty(collect($statusList)->firstWhere('status', '=', $value))) {
            $fail('Invalid status given.');
        }
    }
}
