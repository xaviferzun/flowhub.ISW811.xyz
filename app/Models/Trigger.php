<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trigger extends Model
{
    use HasFactory;

    protected $fillable = ['automation_id', 'type', 'config', 'last_checked_at'];

    protected $casts = [
        'config' => 'array',
        'last_checked_at' => 'datetime',
    ];

    public function automation(): BelongsTo
    {
        return $this->belongsTo(Automation::class);
    }
}