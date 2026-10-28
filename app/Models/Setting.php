<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    public const API_KEY = 'ANTHROPIC_API_KEY';

    protected $fillable = ['key', 'value'];

    /**
     * Only the API key is a secret, so only its value is stored encrypted.
     */
    protected function value(): Attribute
    {
        return Attribute::make(
            get: function (?string $value, array $attributes) {
                if ($value === null || ($attributes['key'] ?? null) !== self::API_KEY) {
                    return $value;
                }

                try {
                    return Crypt::decryptString($value);
                } catch (DecryptException) {
                    return null;
                }
            },
            set: fn (?string $value, array $attributes) => $value !== null && ($attributes['key'] ?? null) === self::API_KEY
                ? Crypt::encryptString($value)
                : $value,
        );
    }

    /**
     * The saved value when it is non-empty, otherwise the .env default.
     */
    public static function resolve(string $key): mixed
    {
        $stored = static::query()->where('key', $key)->first()?->value;

        return $stored !== null && $stored !== '' ? $stored : config('claude.'.$key);
    }
}
