<?php

namespace App\Models;

use Database\Factories\DeviceTypeFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static DeviceTypeFactory factory($count = null, $state = [])
 *
 * @mixin \Eloquent
 */
class DeviceType extends Model
{
    /** @use HasFactory<DeviceTypeFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
    ];

    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }
}
