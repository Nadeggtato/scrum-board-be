<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectConfiguration extends Model
{
    use HasUuids;

    public const STATUS_TO_DO = 'To Do';

    public const DEFAULT_TASK_STATUSES = [
        [
            'status' => self::STATUS_TO_DO,
            'color' => '#EBD7D3',
        ], [
            'status' => 'In Progress',
            'color' => '#FAFFAD',
        ], [
            'status' => 'Done',
            'color' => '#4F734B',
        ], [
            'status' => 'Blocked',
            'color' => '#781717',
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
