<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'value',
        'commentable_type',
        'commentable_id',
        'user_id',
    ];

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }
}
