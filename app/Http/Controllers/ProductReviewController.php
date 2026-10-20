<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'body' => ['required', 'string', 'max:1000'],
        ], [
            'rating.required' => 'Silakan pilih rating dari 1 sampai 5.',
            'rating.integer' => 'Rating harus berupa bilangan bulat.',
            'rating.min' => 'Rating paling rendah adalah 1.',
            'rating.max' => 'Rating paling tinggi adalah 5.',
            'body.required' => 'Silakan tulis ulasan Anda.',
            'body.string' => 'Ulasan harus berupa teks.',
            'body.max' => 'Ulasan maksimal 1.000 karakter.',
        ]);

        $user = $request->user();
        $isEligible = $user->orders()
            ->where('status', Order::STATUS_COMPLETED)
            ->whereHas('item', function (Builder $query) use ($product): void {
                $query->where('product_id', $product->getKey());
            })
            ->exists();

        if (! $isEligible) {
            throw ValidationException::withMessages([
                'review' => 'Ulasan hanya dapat diberikan untuk produk dari pesanan Anda yang sudah selesai.',
            ]);
        }

        if ($user->productReviews()->where('product_id', $product->getKey())->exists()) {
            throw ValidationException::withMessages([
                'review' => 'Anda sudah memberikan ulasan untuk produk ini.',
            ]);
        }

        try {
            $product->reviews()->create([
                'user_id' => $user->getKey(),
                'rating' => $validated['rating'],
                'body' => $validated['body'],
            ]);
        } catch (UniqueConstraintViolationException) {
            // Unique index menjadi perlindungan terakhir bila dua permintaan ulasan tiba bersamaan.
            throw ValidationException::withMessages([
                'review' => 'Anda sudah memberikan ulasan untuk produk ini.',
            ]);
        }

        return redirect()->route('products.show', $product)
            ->with('success', 'Terima kasih, ulasan Anda berhasil dikirim.');
    }
}
