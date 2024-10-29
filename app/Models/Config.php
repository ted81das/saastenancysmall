<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    public static function get(string $key): ?string
    {
        $config = self::where('key', $key)->first();
        if ($config) {
            return $config->value;
        }

        return null;
    }

    public static function set(string $key, ?string $value): void
    {
        $config = self::where('key', $key)->first();
        if ($config) {
            $config->value = $value;
            $config->save();
        } else {
            self::create([
                'key' => $key,
                'value' => $value,
            ]);
        }
    }
}
