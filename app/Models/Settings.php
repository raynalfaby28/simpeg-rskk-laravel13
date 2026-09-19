<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['value' => 'string'];

    public static function get(?string $key, mixed $default = null): mixed
    {
        if ($key === null) {
            return self::pluck('value', 'key')->all();
        }
        return self::where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => (string) $value]);
    }
}