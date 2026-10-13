<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['category_id', 'name', 'name_bn', 'slug', 'short_description', 'description', 'image_path', 'gallery', 'ingredients', 'brewing_guide', 'storage_instructions', 'is_featured', 'is_active', 'sort_order'];
    protected function casts(): array { return ['gallery' => 'array', 'is_featured' => 'boolean', 'is_active' => 'boolean']; }
    public function getImageUrlAttribute(): string { return $this->image_path && !str_starts_with($this->image_path, 'media/') ? asset('storage/'.$this->image_path) : asset('media/tehani-logo-dark.png'); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function variants(): HasMany { return $this->hasMany(ProductVariant::class); }
}
