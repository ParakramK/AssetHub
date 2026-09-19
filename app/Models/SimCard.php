<?php

namespace App\Models;

use App\Enums\SimProvider;
use App\Enums\SimStatus;
use Database\Factories\SimCardFactory;
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
 * @property string|null $current_employee_id
 * @property SimProvider $provider
 * @property string $number
 * @property SimStatus $present_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Company $company
 * @property-read Employee|null $currentEmployee
 * @property-read Collection<int, SimAssignment> $assignments
 *
 * @method static SimCardFactory factory($count = null, $state = [])
 *
 * @mixin \Eloquent
 */
class SimCard extends Model
{
    /** @use HasFactory<SimCardFactory> */
    use Auditable, HasFactory, HasUuids;

    protected $fillable = [
        'company_id',
        'current_employee_id',
        'provider',
        'number',
        'present_status',
    ];

    protected function casts(): array
    {
        return [
            'provider' => SimProvider::class,
            'present_status' => SimStatus::class,
        ];
    }

    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<Employee, $this> */
    public function currentEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'current_employee_id');
    }

    /** @return HasMany<SimAssignment, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(SimAssignment::class);
    }
}
