<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(12);

        return view('catalog.index', compact('products'));
    }

    public function show(Product $product): View
    {
        $product->loadAvg('reviews', 'rating');
        $product->loadCount('reviews');
        $reviews = $product->reviews()
            ->with('user:id,name')
            ->latest()
            ->paginate(10);

        return view('catalog.show', compact('product', 'reviews'));
    }
}
