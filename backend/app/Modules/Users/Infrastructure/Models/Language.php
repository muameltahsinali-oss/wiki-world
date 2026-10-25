<?php

declare(strict_types=1);

namespace App\Modules\Users\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'code',
        'name_en',
        'name_native',
        'direction',
        'numeral_system',
        'is_primary',
        'is_active',
        'fallback_language_id',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
