<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Condition extends Model
{
    use HasFactory;

    protected $fillable = ['automation_id', 'field', 'operator', 'value'];

    public function automation(): BelongsTo
    {
        return $this->belongsTo(Automation::class);
    }
}