<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        // Decrypt value if it exists
        try {
            return decrypt($setting->value);
        } catch (\Exception $e) {
            return $setting->value;
        }
    }

    /**
     * Set a setting value by key.
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $description
     * @return self
     */
    public static function set(string $key, $value, ?string $description = null)
    {
        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => encrypt($value),
                'description' => $description,
            ]
        );
    }
}
