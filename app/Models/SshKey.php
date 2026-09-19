<?php

namespace App\Models;

use Database\Factories\SshKeyFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $server_id
 * @property string $name
 * @property string|null $public_key
 * @property string $private_key
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Server $server
 *
 * @method static SshKeyFactory factory($count = null, $state = [])
 *
 * @mixin \Eloquent
 */
class SshKey extends Model
{
    /** @use HasFactory<SshKeyFactory> */
    use Auditable, HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'server_id',
        'name',
        'public_key',
        'private_key',
    ];

    /**
     * Attributes hidden from serialization, so the decrypted private key
     * never leaks into API responses, Inertia props, or logs.
     *
     * @var list<string>
     */
    protected $hidden = [
        'private_key',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'private_key' => 'encrypted',
        ];
    }

    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }

    /**
     * @return BelongsTo<Server, $this>
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
