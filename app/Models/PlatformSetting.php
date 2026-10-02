<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PlatformSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $all = Cache::remember('platform_settings', 60, function () {
            return static::query()->pluck('value', 'key')->all();
        });

        return $all[$key] ?? $default;
    }

    public static function setValue(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => is_scalar($value) ? (string) $value : json_encode($value)]);
        Cache::forget('platform_settings');
    }

    public static function number(string $key, float $default = 0): float
    {
        return (float) static::getValue($key, $default);
    }
}
