<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @method static Builder<static>|Company newModelQuery()
 * @method static Builder<static>|Company newQuery()
 * @method static Builder<static>|Company query()
 *
 * @mixin \Eloquent
 */
class Company extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'companies';

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
    ];

    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }
}
