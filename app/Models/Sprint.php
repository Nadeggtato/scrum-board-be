<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sprint extends Model
{
    use HasUuids;

    const ALLOWED_INCLUDES = [
        'project',
        'userStories',
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
