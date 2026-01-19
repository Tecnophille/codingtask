<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'items',
        'pickup_time',
        'is_vip',
        'status',
    ];

    protected $casts = [
        'items' => 'array',
        'is_vip' => 'boolean',
        'pickup_time' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
