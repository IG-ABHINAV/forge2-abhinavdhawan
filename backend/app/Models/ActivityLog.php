<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'action',
        'entity_type',
        'entity_id',
        'payload',
        'user_id',
        'organization_id',
        'created_at',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('organization', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('activity_logs.organization_id', auth()->user()->organization_id);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
