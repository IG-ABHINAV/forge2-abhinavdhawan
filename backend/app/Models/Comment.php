<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'body',
        'is_internal',
        'ticket_id',
        'user_id',
        'organization_id',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('organization', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('comments.organization_id', auth()->user()->organization_id);
            }
        });
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
