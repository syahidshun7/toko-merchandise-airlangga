<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Database\ForeignKeyConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::query()->withCount('products')->orderBy('name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => new Category(['is_active' => true])]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = (Str::limit(Str::slug($data['name']), 200, '') ?: 'kategori').'-'.Str::lower((string) Str::ulid());
        Category::create($data);

        return to_route('admin.categories.index')->with('status', 'Kategori ditambahkan.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return to_route('admin.categories.index')->with('status', 'Kategori diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        try {
            $deleted = DB::transaction(function () use ($category) {
                $locked = Category::query()->lockForUpdate()->findOrFail($category->id);

                if ($locked->products()->withTrashed()->exists()) {
                    return false;
                }

                return $locked->delete();
            });
        } catch (ForeignKeyConstraintViolationException) {
            $deleted = false;
        }

        if (! $deleted) {
            return back()->withErrors(['category' => 'Kategori masih digunakan oleh produk, termasuk produk yang diarsipkan.']);
        }

        return to_route('admin.categories.index')->with('status', 'Kategori dihapus.');
    }
}
