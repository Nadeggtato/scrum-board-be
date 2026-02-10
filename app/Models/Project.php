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

    protected $fillable = [
        'name',
        'is_active',
        'creator_id',
    ];

    protected static function booted(): void
    {
        self::creating(static function (Project $project): void {
            $project->creator_id = auth('sanctum')->user()->id;
        });
    }

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
        return $this->belongsToMany(ProjectMember::class);
    }

    public function sprints(): HasMany
    {
        return $this->hasMany(Sprint::class);
    }
}
