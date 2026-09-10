<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'event',
        'web_enabled',
        'push_enabled',
        'whatsapp_enabled',
    ];

    protected function casts(): array
    {
        return [
            'web_enabled' => 'boolean',
            'push_enabled' => 'boolean',
            'whatsapp_enabled' => 'boolean',
        ];
    }

    public static function isChannelEnabled(string $event, string $channel): bool
    {
        $setting = static::where('event', $event)->first();
        if (!$setting) {
            return true; // Default enabled
        }

        return match ($channel) {
            'web' => (bool) $setting->web_enabled,
            'push' => (bool) $setting->push_enabled,
            'whatsapp' => (bool) $setting->whatsapp_enabled,
            default => true,
        };
    }
}
