<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasUuids, SoftDeletes;

    public const ALLOWED_INCLUDES = [
        'assignee',
        'userStory',
    ];

    protected $fillable = [
        'title',
        'description',
        'status',
        'user_id',
        'user_story_id',
    ];

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userStory(): BelongsTo
    {
        return $this->belongsTo(UserStory::class);
    }
}
