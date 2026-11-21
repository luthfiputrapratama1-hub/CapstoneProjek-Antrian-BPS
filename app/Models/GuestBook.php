<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestBook extends Model
{
    protected $fillable = [
        'queue_id', 'full_name', 'phone_number', 'email',
        'institution_name', 'institution_type', 'data_needs_description'
    ];

    public function queue(): BelongsTo {
        return $this->belongsTo(Queue::class);
    }
}
