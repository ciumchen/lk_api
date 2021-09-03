<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Jobs
 *
 * @property int $id
 * @property string $queue
 * @property string $payload
 * @property int $attempts
 * @property int|null $reserved_at
 * @property int $available_at
 * @property \Illuminate\Support\Carbon $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs query()
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs whereAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs whereAvailableAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs whereQueue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jobs whereReservedAt($value)
 * @mixin \Eloquent
 */
class Jobs extends Model
{
    use HasFactory;

    protected $table = 'jobs';
}
