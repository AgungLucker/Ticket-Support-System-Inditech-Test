<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaRule extends Model
{
    protected $fillable = [
        'priority_id',
        'response_time_hours',
        'resolution_time_hours',
    ];

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }
}
