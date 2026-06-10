<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SlaRule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'priority_id',
        'response_time_hours',
        'resolution_time_hours',
    ];

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class)->withTrashed();
    }
}
