<?php

namespace App\Models;

use App\Enums\DeletionRequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountDeletionRequest extends Model
{
    protected $fillable = [
        'user_id',
        'reason',
        'status',
        'decided_by',
        'decided_at',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'status' => DeletionRequestStatus::class,
            'decided_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', DeletionRequestStatus::PENDING->value);
    }
}
