<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $products = Product::query()->withSum('variants', 'stock');
        $stock = Product::query()->select('products.id')
            ->selectRaw('COALESCE((SELECT SUM(stock) FROM product_variants WHERE product_id = products.id), 0) AS total_stock');
        $inventory = Product::query()->getQuery()->newQuery()->fromSub($stock, 'inventory');
        $threshold = config('store.low_stock_threshold');

        return view('admin.dashboard', [
            'stats' => [
                'Total produk' => Product::count(),
                'Total kategori' => Category::count(),
                'Produk aktif' => Product::where('is_active', true)->count(),
                'Produk nonaktif' => Product::where('is_active', false)->count(),
                'Stok habis' => (clone $inventory)->where('total_stock', 0)->count(),
                'Stok menipis' => (clone $inventory)->whereBetween('total_stock', [1, $threshold])->count(),
            ],
            'latest' => $products->with('category')->latest()->orderByDesc('id')->limit(6)->get(),
            'threshold' => $threshold,
        ]);
    }
}
