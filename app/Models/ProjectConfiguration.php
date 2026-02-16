<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectConfiguration extends Model
{
    use HasUuids;

    public const TYPE_TASK_STATUSES = 'task_statuses';

    public const STATUS_TO_DO = 'To Do';

    public const DEFAULT_TASK_STATUSES = [
        [
            'status' => self::STATUS_TO_DO,
            'color' => '#EBD7D3',
            'can_delete' => false,
        ], [
            'status' => 'In Progress',
            'color' => '#FAFFAD',
            'can_delete' => false,
        ], [
            'status' => 'Done',
            'color' => '#4F734B',
            'can_delete' => false,
        ], [
            'status' => 'Blocked',
            'color' => '#781717',
            'can_delete' => false,
        ],
    ];

    protected $fillable = [
        'type',
        'value',
        'project_id',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
