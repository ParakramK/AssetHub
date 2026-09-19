<?php

namespace App\Models;

use Database\Factories\SimAssignmentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $sim_card_id
 * @property string $employee_id
 * @property Carbon $assigned_at
 * @property Carbon|null $returned_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read SimCard $simCard
 * @property-read Employee $employee
 *
 * @method static SimAssignmentFactory factory($count = null, $state = [])
 *
 * @mixin \Eloquent
 */
class SimAssignment extends Model
{
    /** @use HasFactory<SimAssignmentFactory> */
    use Auditable, HasFactory, HasUuids;

    protected $fillable = [
        'sim_card_id',
        'employee_id',
        'assigned_at',
        'returned_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }

    /** @return BelongsTo<SimCard, $this> */
    public function simCard(): BelongsTo
    {
        return $this->belongsTo(SimCard::class);
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function isOpen(): bool
    {
        return $this->returned_at === null;
    }
}
