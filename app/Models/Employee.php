<?php

namespace App\Models;

use Database\Factories\EmployeeFactory;
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
 * @property string $name
 * @property string $email
 * @property string|null $mobile_no
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Company $company
 * @property-read Collection<int, SimAssignment> $simAssignments
 * @property-read Collection<int, SimCard> $currentSimCards
 * @property-read Collection<int, DeviceAssignment> $deviceAssignments
 * @property-read Collection<int, Device> $currentDevices
 *
 * @method static EmployeeFactory factory($count = null, $state = [])
 *
 * @mixin \Eloquent
 */
class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use Auditable, HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'email',
        'mobile_no',
        'company_id',
    ];

    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return HasMany<SimAssignment, $this> */
    public function simAssignments(): HasMany
    {
        return $this->hasMany(SimAssignment::class);
    }

    /** @return HasMany<SimCard, $this> */
    public function currentSimCards(): HasMany
    {
        return $this->hasMany(SimCard::class, 'current_employee_id');
    }

    /** @return HasMany<DeviceAssignment, $this> */
    public function deviceAssignments(): HasMany
    {
        return $this->hasMany(DeviceAssignment::class);
    }

    /** @return HasMany<Device, $this> */
    public function currentDevices(): HasMany
    {
        return $this->hasMany(Device::class, 'current_employee_id');
    }
}
