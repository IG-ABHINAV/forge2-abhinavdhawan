<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'priority',
        'response_hours',
        'resolution_hours',
        'organization_id',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('organization', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('sla_policies.organization_id', auth()->user()->organization_id);
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
