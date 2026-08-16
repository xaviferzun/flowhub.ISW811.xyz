<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Automation extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'is_active'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trigger(): HasOne
    {
        return $this->hasOne(Trigger::class);
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(Condition::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(Action::class)->orderBy('order');
    }
}