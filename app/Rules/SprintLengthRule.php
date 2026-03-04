<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;

class SprintLengthRule implements ValidationRule
{
    private Carbon $startDate;

    public function __construct(Carbon $startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $diffInWeeks = $this->startDate->diffInWeeks(Carbon::parse($value));

        if ($diffInWeeks < 1) {
            $fail('A sprint must be at least a week long.');
        }

        if ($diffInWeeks > 4) {
            $fail('A sprint cannot be more than four weeks.');
        }
    }
}
