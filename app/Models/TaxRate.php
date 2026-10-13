<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    protected $fillable = ['name', 'percentage', 'is_inclusive', 'is_active'];
    protected function casts(): array { return ['percentage' => 'decimal:2', 'is_inclusive' => 'boolean', 'is_active' => 'boolean']; }
}
