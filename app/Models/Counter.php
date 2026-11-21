<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Counter extends Model
{
    protected $fillable = ['name', 'counter_number', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function services(): BelongsToMany {
        return $this->belongsToMany(Service::class, 'counter_services');
    }

    public function queues(): HasMany {
        return $this->hasMany(Queue::class);
    }
}
