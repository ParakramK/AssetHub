<?php

namespace App\Models;

use Database\Factories\ServerCredentialFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $server_id
 * @property string $username
 * @property string $password
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Server $server
 *
 * @method static ServerCredentialFactory factory($count = null, $state = [])
 *
 * @mixin \Eloquent
 */
class ServerCredential extends Model
{
    /** @use HasFactory<ServerCredentialFactory> */
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'server_id',
        'username',
        'password',
    ];

    /**
     * Attributes hidden from serialization, so the decrypted secret never
     * leaks into API responses, Inertia props, or logs.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
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
