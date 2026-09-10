<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlaSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'priority',
        'first_response_hours',
        'resolution_hours',
    ];

    protected function casts(): array
    {
        return [
            'first_response_hours' => 'integer',
            'resolution_hours' => 'integer',
        ];
    }
}
