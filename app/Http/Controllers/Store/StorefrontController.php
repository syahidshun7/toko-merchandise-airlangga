<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\StoreSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    public function home(): View
    {
        $products = Product::visibleInStore()->withStoreCard();

        return view('store.home', [
            'banners' => Banner::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get(),
            'setting' => StoreSetting::forStorefront(),
            'categories' => $this->categories()->limit(8)->get(),
            'featured' => (clone $products)->where('is_featured', true)->latest()->orderByDesc('id')->limit(4)->get(),
            'latest' => $products->latest()->orderByDesc('id')->limit(4)->get(),
        ]);
    }

    public function preorder(): View
    {
        return view('store.preorder', [
            'setting' => StoreSetting::forStorefront(),
            'products' => Product::visibleInStore()->withStoreCard()->where('is_preorder', true)
                ->latest()->orderByDesc('id')->paginate(12),
        ]);
    }

    public function catalog(Request $request, ?string $slug = null): View
    {
        $search = mb_substr(trim($this->queryString($request, 'q')), 0, 100);
        $sort = $this->queryString($request, 'sort');
        $categorySlug = $slug ?? $this->queryString($request, 'kategori');
        $category = $categorySlug === '' ? null : $this->categories()->where('slug', $categorySlug)->firstOrFail();

        $products = Product::visibleInStore()->withStoreCard();

        if ($search !== '') {
            $literal = strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']);
            $products->whereRaw("products.name LIKE ? ESCAPE '!'", ['%'.$literal.'%']);
        }

        if ($category) {
            $products->where('category_id', $category->id);
        }

        match ($sort) {
            'termurah' => $products->orderBy('starting_price'),
            'termahal' => $products->orderByDesc('starting_price'),
            default => $products->latest(),
        };

        return view('store.catalog', [
            'setting' => StoreSetting::forStorefront(),
            'products' => $products->orderByDesc('id')->paginate(12)->withQueryString(),
            'categories' => $this->categories()->get(),
            'category' => $category,
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    public function show(string $slug): View
    {
        $product = Product::visibleInStore()->withStoreCard()
            ->with(['images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order')->orderBy('id'), 'variants'])
            ->where('slug', $slug)->firstOrFail();

        return view('store.product', [
            'setting' => StoreSetting::forStorefront(),
            'product' => $product,
            'related' => Product::visibleInStore()->withStoreCard()
                ->where('category_id', $product->category_id)->whereKeyNot($product->id)
                ->latest()->orderByDesc('id')->limit(4)->get(),
        ]);
    }

    private function queryString(Request $request, string $key): string
    {
        $value = $request->query($key);

        return is_string($value) ? $value : '';
    }

    private function categories(): Builder
    {
        return Category::query()->where('is_active', true)->orderBy('name')->orderBy('id');
    }
}
