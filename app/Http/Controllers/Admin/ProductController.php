<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::query()->latest()->paginate(15);

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        return view('admin.products.create', ['product' => new Product()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedProduct($request);
        $photoPath = null;

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('products', 'public');
        }

        unset($validated['photo']);

        try {
            DB::transaction(fn () => Product::create($validated + ['photo_path' => $photoPath]));
        } catch (Throwable $exception) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            throw $exception;
        }

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product): View
    {
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validatedProduct($request);
        $oldPhotoPath = $product->photo_path;
        $newPhotoPath = null;

        if ($request->hasFile('photo')) {
            $newPhotoPath = $request->file('photo')->store('products', 'public');
            $validated['photo_path'] = $newPhotoPath;
        }

        unset($validated['photo']);

        try {
            DB::transaction(fn () => $product->update($validated));
        } catch (Throwable $exception) {
            if ($newPhotoPath) {
                Storage::disk('public')->delete($newPhotoPath);
            }

            throw $exception;
        }

        if ($newPhotoPath && $oldPhotoPath) {
            Storage::disk('public')->delete($oldPhotoPath);
        }

        return redirect()->route('admin.products.show', $product)->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->update(['is_active' => false]);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diarsipkan.');
    }

    private function validatedProduct(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'stock' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'is_active' => ['required', Rule::in(['0', '1', 0, 1, true, false])],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
    }
}