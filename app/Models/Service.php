<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    protected $fillable = ['name', 'code_prefix', 'description', 'is_active', 'order_priority'];
    protected $casts = ['is_active' => 'boolean'];

    public function queues(): HasMany {
        return $this->hasMany(Queue::class);
    }

    public function counters(): BelongsToMany {
        return $this->belongsToMany(Counter::class, 'counter_services');
    }
}
