<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sprint extends Model
{
    use HasUuids;

    public const ALLOWED_INCLUDES = [
        'project',
        'userStories',
    ];

    public const PATTERN_INCREMENTAL = 0;

    public const PATTERN_WEEK_NUMBER = 1;

    public const NAMING_PATTERNS = [
        self::PATTERN_INCREMENTAL,
        self::PATTERN_WEEK_NUMBER,
    ];

    protected $fillable = [
        'name',
        'start',
        'end',
        'project_id',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function userStories(): HasMany
    {
        return $this->hasMany(UserStory::class);
    }
}
