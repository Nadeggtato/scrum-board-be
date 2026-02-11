<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasUuids;

    public const ALLOWED_INCLUDES = [
        'creator',
        'members',
        'sprints',
    ];

    protected $fillable = [
        'name',
        'is_active',
        'creator_id',
    ];

    public function configurations(): HasMany
    {
        return $this->hasMany(ProjectConfiguration::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->using(ProjectMember::class)
            ->wherePivotNull('deleted_at');
    }

    public function sprints(): HasMany
    {
        return $this->hasMany(Sprint::class);
    }
}
