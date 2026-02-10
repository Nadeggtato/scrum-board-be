<?php

namespace App\Support;

class ParseIncludes
{
    public function __invoke(string $value, array $allowed): array
    {
        return collect(explode(',', $value))
            ->intersect($allowed)
            ->toArray();
    }
}
