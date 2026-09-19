<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * @property string $id
 * @property string $name
 * @property string $guard_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @mixin Eloquent
 */
class Permission extends SpatiePermission
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'guard_name',
    ];

    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }
}
