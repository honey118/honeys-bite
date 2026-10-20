<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $product->name }} | Honey's Bite</title>
    <style>
        :root{font-family:"Instrument Sans","Segoe UI",sans-serif;color:#38291f;background:#f5eee3}*{box-sizing:border-box}body{margin:0;min-width:320px}.topbar{display:flex;justify-content:space-between;gap:18px;padding:18px clamp(18px,6vw,78px);background:#543b2d;color:#fff8ee}.brand{font:600 24px Georgia,serif;color:inherit;text-decoration:none}.topbar a:not(.brand){color:#f1dec8;text-underline-offset:4px}.page{width:min(900px,100%);margin:0 auto;padding:36px 24px 64px}.panel{margin-top:18px;padding:clamp(20px,4vw,30px);border:1px solid #dfd0bc;background:#fffdf9}.product{display:grid;grid-template-columns:minmax(0,1fr) 1fr;gap:26px;align-items:center}.photo{width:100%;aspect-ratio:4/3;object-fit:cover;background:#eee2d1}.photo-empty{display:grid;place-items:center;color:#8b674d;font-size:13px}.eyebrow{margin:0 0 8px;color:#8b674d;font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}h1{margin:0;font:400 clamp(30px,5vw,42px) Georgia,serif}.description,.review-body{color:#76685e;font-size:14px;line-height:1.65;white-space:pre-line}.price{color:#684a34;font-size:19px;font-weight:700}.rating{margin-top:16px;color:#624932}.status,.flash{display:block;margin-top:16px;padding:10px 12px;background:#f2e8dc;color:#624932;font-size:13px}.flash{border:1px solid #bdd0b0;background:#f1f7ed;color:#385332}.button{display:inline-block;margin-top:16px;padding:11px 15px;background:#654733;color:#fffaf3;text-decoration:none;font-size:13px;font-weight:700}.section-title{margin:0;font:400 24px Georgia,serif}.review{padding:18px 0;border-bottom:1px solid #eadfd1}.review:last-child{border-bottom:0}.review-head{display:flex;justify-content:space-between;gap:16px}.review-author{font-weight:700}.muted{margin-top:5px;color:#76685e;font-size:12px}.empty{padding:20px 0;color:#76685e}.pagination{margin-top:20px}.pagination nav{display:flex;justify-content:space-between}.back{display:inline-block;margin-top:22px;color:#654733;font-weight:700;text-underline-offset:3px}@media(max-width:620px){.page{padding:28px 16px 48px}.product{grid-template-columns:1fr;gap:18px}}
    </style>
    @include('partials.responsive-polish')
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ route('catalog.index') }}">Honey's Bite</a>
        <a href="{{ route('catalog.index') }}">Katalog</a>
    </header>
    <main class="page">
        @if (session('success'))<p class="flash" role="status">{{ session('success') }}</p>@endif
        @if (session('error'))<p class="status" role="alert">{{ session('error') }}</p>@endif
        <section class="panel product">
            @if ($product->photo_path)
                <img class="photo" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->photo_path) }}" alt="Foto {{ $product->name }}">
            @else
                <div class="photo photo-empty" aria-hidden="true">Honey's Bite</div>
            @endif
            <div>
                <p class="eyebrow">Detail produk</p>
                <h1>{{ $product->name }}</h1>
                <p class="description">{{ $product->description ?: 'Dibuat segar dengan perhatian dari dapur kami.' }}</p>
                <p class="price">Rp{{ number_format((float) $product->price, 0, ',', '.') }}</p>
                <p class="rating">
                    @if ($product->reviews_count > 0)
                        Rating {{ number_format((float) $product->reviews_avg_rating, 1) }} dari 5 · {{ $product->reviews_count }} ulasan
                    @else
                        Belum ada ulasan
                    @endif
                </p>
                @if ($product->is_active)
                    @if ($product->stock > 0)
                        <a class="button" href="{{ route('checkout.create', $product) }}">Pesan produk</a>
                    @else
                        <span class="status">Stok habis · tidak menerima pesanan baru</span>
                    @endif
                @else
                    <span class="status">Produk diarsipkan · tidak menerima pesanan baru</span>
                @endif
            </div>
        </section>

        <section class="panel">
            <h2 class="section-title">Ulasan pelanggan</h2>
            @forelse ($reviews as $review)
                <article class="review">
                    <div class="review-head">
                        <span class="review-author">{{ $review->user->name }}</span>
                        <span>Rating {{ $review->rating }} dari 5</span>
                    </div>
                    <p class="review-body">{{ $review->body }}</p>
                    <p class="muted">{{ $review->created_at->format('d M Y') }}</p>
                </article>
            @empty
                <p class="empty">Belum ada ulasan untuk produk ini.</p>
            @endforelse
            <div class="pagination">{{ $reviews->links() }}</div>
        </section>
        <a class="back" href="{{ route('catalog.index') }}">Kembali ke katalog</a>
    </main>
</body>
</html>
