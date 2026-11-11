<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Registration extends Model
{
    protected $fillable = [
        'full_name',
        'source',
        'checked_in_at',
        'notes',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Registration $r) {
            $r->full_name = static::normalizeName($r->full_name);
            $r->name_key = static::keyFor($r->full_name);
        });
    }

    public static function normalizeName(?string $name): string
    {
        return trim(preg_replace('/\s+/u', ' ', (string) $name));
    }

    public static function keyFor(string $name): string
    {
        return Str::lower(static::normalizeName($name));
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        $term = trim((string) $term);

        return $term === ''
            ? $q
            : $q->where(fn ($w) => $w->where('full_name', 'like', "%{$term}%")
                ->orWhere('notes', 'like', "%{$term}%"));
    }

    public function scopeStatus(Builder $q, ?string $status): Builder
    {
        return match ($status) {
            'checked_in' => $q->whereNotNull('checked_in_at'),
            'pending' => $q->whereNull('checked_in_at'),
            default => $q,
        };
    }
}
