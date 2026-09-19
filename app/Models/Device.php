<?php

namespace App\Models;

use App\Enums\DeviceStatus;
use Database\Factories\DeviceFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $company_id
 * @property string $device_type_id
 * @property string|null $current_employee_id
 * @property string $brand
 * @property string $model
 * @property string $code
 * @property string|null $imei
 * @property string|null $mac_address
 * @property string|null $serial_no
 * @property DeviceStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Company $company
 * @property-read DeviceType $type
 * @property-read Employee|null $currentEmployee
 * @property-read Collection<int, DeviceAssignment> $assignments
 *
 * @method static DeviceFactory factory($count = null, $state = [])
 *
 * @mixin Eloquent
 */
class Device extends Model
{
    /** @use HasFactory<DeviceFactory> */
    use Auditable, HasFactory, HasUuids;

    protected $fillable = [
        'company_id',
        'device_type_id',
        'current_employee_id',
        'brand',
        'model',
        'code',
        'imei',
        'mac_address',
        'serial_no',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => DeviceStatus::class,
        ];
    }

    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }

    protected static function booted(): void
    {
        static::creating(function (Device $device) {
            if ($device->code === null) {
                do {
                    $device->code = 'AST-'.Str::upper(Str::random(8));
                } while (static::where('code', $device->code)->exists());
            }

            $device->status ??= DeviceStatus::Available;
        });
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<DeviceType, $this> */
    public function type(): BelongsTo
    {
        return $this->belongsTo(DeviceType::class, 'device_type_id');
    }

    /** @return BelongsTo<Employee, $this> */
    public function currentEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'current_employee_id');
    }

    /** @return HasMany<DeviceAssignment, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(DeviceAssignment::class);
    }
}
