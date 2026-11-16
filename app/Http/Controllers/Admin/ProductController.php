<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductWriter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('admin.products.index', [
            'products' => Product::query()->with('category')->withSum('variants', 'stock')
                ->latest()->orderByDesc('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Product(['is_active' => true, 'is_featured' => false]));
    }

    public function store(ProductRequest $request, ProductWriter $writer): RedirectResponse
    {
        $product = $writer->save(new Product, $request->validated());

        return to_route('admin.products.show', $product)->with('status', 'Produk ditambahkan.');
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'variants', 'images' => fn ($query) => $query->orderBy('sort_order')->orderBy('id')]);

        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        return $this->form($product->load(['variants', 'images' => fn ($query) => $query->orderBy('sort_order')->orderBy('id')]));
    }

    public function update(ProductRequest $request, Product $product, ProductWriter $writer): RedirectResponse
    {
        $writer->save($product, $request->validated());

        return to_route('admin.products.show', $product)->with('status', 'Produk diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return to_route('admin.products.index')->with('status', 'Produk diarsipkan.');
    }

    private function form(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }
}
