<?php

namespace Modules\Gateway\Models;

use App\Support\Traits\Filterable;
use App\Support\Traits\HasFactory;
use App\Support\Traits\MakeAble;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use Filterable, HasFactory, MakeAble, Prunable;

    protected $fillable = [
        'key',
        'name',
        'rate_limit_tier',
        'allowed_ips',
        'expires_at',
        'last_used_at',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'allowed_ips' => 'array',
        'metadata' => 'array',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public static function generate(string $name, array $options = []): self
    {
        return static::create([
            'key' => Str::random(64),
            'name' => $name,
            'rate_limit_tier' => $options['rate_limit_tier'] ?? 'basic',
            'allowed_ips' => $options['allowed_ips'] ?? null,
            'expires_at' => $options['expires_at'] ?? null,
            'is_active' => $options['is_active'] ?? true,
            'metadata' => $options['metadata'] ?? null,
        ]);
    }

    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function markUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    public function prunable()
    {
        return static::where('expires_at', '<', now()->subYear());
    }
}
