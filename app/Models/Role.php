<?php

namespace App\Models;

use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * @property string $id
 * @property string $name
 * @property string $guard_name
 * @property string|null $description
 * @property bool $is_super_admin
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, User> $users
 * @property-read int|null $users_count
 *
 * @method static RoleFactory factory($count = null, $state = [])
 * @method static Builder<static>|Role newModelQuery()
 * @method static Builder<static>|Role newQuery()
 * @method static Builder<static>|Role query()
 * @method static Builder<static>|Role whereCreatedAt($value)
 * @method static Builder<static>|Role whereDescription($value)
 * @method static Builder<static>|Role whereGuardName($value)
 * @method static Builder<static>|Role whereId($value)
 * @method static Builder<static>|Role whereIsSuperAdmin($value)
 * @method static Builder<static>|Role whereName($value)
 * @method static Builder<static>|Role whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Role extends SpatieRole
{
    use Auditable, HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'guard_name',
        'description',
        'is_super_admin',
    ];

    protected function casts(): array
    {
        return [
            'is_super_admin' => 'boolean',
        ];
    }

    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }
}
