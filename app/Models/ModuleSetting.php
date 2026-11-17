<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleSetting extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'category',
        'enabled',
        'is_core',
        'hours_saved_weekly',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'is_core' => 'boolean',
            'hours_saved_weekly' => 'integer',
        ];
    }
}
