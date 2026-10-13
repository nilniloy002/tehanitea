<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    // A zero offer price means no promotion. Keep legacy priced packs working.
    public function getEffectivePriceAttribute(): float {
        $regular = (float) $this->regular_price;
        $offer = (float) $this->price;
        return $offer > 0 && ($regular <= 0 || $offer < $regular) ? $offer : $regular;
    }
    protected static function booted(): void {
        static::created(function(self $v){ if($v->stock_quantity>0) $v->inventoryMovements()->create(['user_id'=>auth()->id(),'quantity_change'=>$v->stock_quantity,'quantity_after'=>$v->stock_quantity,'reason'=>'Opening stock']); });
        static::updated(function(self $v){ if($v->wasChanged('stock_quantity')) $v->inventoryMovements()->create(['user_id'=>auth()->id(),'quantity_change'=>$v->stock_quantity-$v->getOriginal('stock_quantity'),'quantity_after'=>$v->stock_quantity,'reason'=>'Admin stock adjustment']); });
    }
    protected $fillable = ['product_id', 'name', 'sku', 'weight_grams', 'price', 'regular_price', 'stock_quantity', 'low_stock_threshold', 'is_active', 'sort_order'];
    protected function casts(): array { return ['price' => 'decimal:2', 'regular_price' => 'decimal:2', 'is_active' => 'boolean']; }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function inventoryMovements(): HasMany { return $this->hasMany(InventoryMovement::class); }
}
