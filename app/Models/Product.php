<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'base_price',
        'is_active',
        'is_featured',
        'is_preorder',
        'preorder_estimate',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_preorder' => 'boolean',
        ];
    }

    public function scopeVisibleInStore(Builder $query): Builder
    {
        return $query->where('products.is_active', true)
            ->whereHas('category', fn (Builder $category) => $category->where('is_active', true));
    }

    public function scopeWithStoreCard(Builder $query): Builder
    {
        return $query->select('products.*')
            ->selectRaw('COALESCE((SELECT MIN(COALESCE(product_variants.price, products.base_price)) FROM product_variants WHERE product_variants.product_id = products.id), products.base_price) AS starting_price')
            ->with(['category', 'coverImage'])
            ->withSum('variants', 'stock');
    }

    public function coverImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->ofMany([
            'is_primary' => 'max',
            'sort_order' => 'min',
            'id' => 'min',
        ]);
    }

    public function quantityLimit(int $stock): int
    {
        return $this->is_preorder ? 2147483647 : $stock;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
