<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Task extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
      'title',
      'description',
      'status',
      'project_id',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo {
        return $this->belongsTo(Project::class);
    }
}
