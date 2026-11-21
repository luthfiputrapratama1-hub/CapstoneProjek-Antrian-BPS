<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Queue extends Model
{
    protected $fillable = [
        'queue_date', 'ticket_code', 'ticket_number', 'service_id', 'counter_id',
        'user_id', 'status', 'recall_count', 'called_at', 'served_at', 'completed_at',
        'recalled_skipped_at', 'wait_duration_seconds', 'serve_duration_seconds',
        'created_at'
    ];

    protected $casts = [
        'queue_date' => 'date',
        'recall_count' => 'integer',
        'called_at' => 'datetime',
        'served_at' => 'datetime',
        'completed_at' => 'datetime',
        'recalled_skipped_at' => 'datetime',
    ];

    public function service(): BelongsTo {
        return $this->belongsTo(Service::class);
    }

    public function counter(): BelongsTo {
        return $this->belongsTo(Counter::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function guestBook(): HasOne {
        return $this->hasOne(GuestBook::class);
    }
}
