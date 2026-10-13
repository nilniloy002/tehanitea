<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryZone extends Model
{
    protected $fillable = ['name', 'name_bn', 'charge', 'free_delivery_threshold', 'is_active', 'sort_order'];
    protected function casts(): array { return ['charge' => 'decimal:2', 'free_delivery_threshold' => 'decimal:2', 'is_active' => 'boolean']; }
}
