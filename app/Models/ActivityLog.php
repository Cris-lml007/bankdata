<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'event',
        'description',
        'old_values',
        'new_values',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Registrar una actividad para cualquier modelo
     * que utilice el trait HasActivityLogs.
     */
    public static function record(
        Model $model,
        string $event,
        array $oldValues = [],
        array $newValues = [],
        ?string $description = null
    ): self {
        return $model->activityLogs()->create([
            'user_id' => auth()->id(),
            'event' => $event,
            'description' => $description,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
        ]);
    }
}
