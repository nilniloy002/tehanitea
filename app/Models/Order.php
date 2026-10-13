<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['stock_restored' => 'boolean', 'tax_snapshot' => 'array']; }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function payment(): HasOne { return $this->hasOne(Payment::class); }
    public function histories(): HasMany { return $this->hasMany(OrderStatusHistory::class); }
}
