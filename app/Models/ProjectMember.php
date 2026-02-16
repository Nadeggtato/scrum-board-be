<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectMember extends Pivot
{
    use HasUuids, SoftDeletes;

    protected $table = 'project_members';
}
