<?php

declare(strict_types=1);

namespace SolarInvestments\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use SolarInvestments\Enums\HeartbeatType;

/**
 * @property int $id
 * @property \SolarInvestments\Enums\HeartbeatType $type
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\SolarInvestments\Models\Heartbeat job()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\SolarInvestments\Models\Heartbeat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\SolarInvestments\Models\Heartbeat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\SolarInvestments\Models\Heartbeat notStale()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\SolarInvestments\Models\Heartbeat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\SolarInvestments\Models\Heartbeat schedule()
 *
 * @mixin \Eloquent
 */
class Heartbeat extends Model
{
    protected $fillable = ['type'];

    protected function casts(): array
    {
        return [
            'type' => HeartbeatType::class,
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected function scopeJob(Builder $query): void
    {
        $query->where('type', HeartbeatType::Job);
    }

    protected function scopeNotStale(Builder $query): void
    {
        $query->where('updated_at', '>', now()->subMinutes(5));
    }

    protected function scopeSchedule(Builder $query): void
    {
        $query->where('type', HeartbeatType::Schedule);
    }
}
