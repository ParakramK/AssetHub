<?php

namespace App\Models;

use Database\Factories\DomainFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $company_id
 * @property string|null $created_by
 * @property string $domain_name
 * @property string|null $registrar
 * @property Carbon|null $expiry_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Company $company
 * @property-read User|null $creator
 *
 * @method static DomainFactory factory($count = null, $state = [])
 * @method static Builder<static>|Domain newModelQuery()
 * @method static Builder<static>|Domain newQuery()
 * @method static Builder<static>|Domain query()
 *
 * @mixin Eloquent
 */
class Domain extends Model
{
    use Auditable, HasFactory, HasUuids;

    protected $fillable = [
        'company_id',
        'created_by',
        'domain_name',
        'registrar',
        'expiry_date',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
        ];
    }

    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
