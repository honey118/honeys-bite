<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Katalog | Honey's Bite</title>
    <style>
        :root{font-family:"Instrument Sans","Segoe UI",sans-serif;color:#38291f;background:#f5eee3;font-synthesis:none;text-rendering:optimizeLegibility}*{box-sizing:border-box}body{margin:0;min-width:320px}.topbar{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:20px clamp(18px,6vw,78px);background:#543b2d;color:#fff8ee}.brand{font:600 26px Georgia,serif;color:inherit;text-decoration:none}.topbar nav{display:flex;gap:20px}.topbar a:not(.brand){color:#f1dec8;font-size:13px;text-underline-offset:4px}.intro{padding:58px clamp(18px,8vw,110px) 44px;background:#eee2d1}.eyebrow{margin:0 0 10px;color:#8b674d;font-size:12px;font-weight:700;letter-spacing:.14em;text-transform:uppercase}h1{margin:0;font:400 clamp(36px,5vw,54px) Georgia,serif}.intro-copy{max-width:540px;margin:14px 0 0;color:#716155;font-size:15px;line-height:1.65}.catalog{width:min(1180px,100%);margin:0 auto;padding:36px 24px 64px}.catalog-head{display:flex;align-items:baseline;justify-content:space-between;gap:16px;margin-bottom:22px}.catalog-head h2{margin:0;font:400 27px Georgia,serif}.count{color:#76685e;font-size:13px}.grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px}.product{overflow:hidden;border:1px solid #dfd0bc;background:#fffdf9}.product-photo{display:block;width:100%;aspect-ratio:4/3;object-fit:cover;background:#eee2d1}.photo-empty{display:grid;place-items:center;color:#8b674d;font-size:13px}.product-body{padding:18px}.product h3{margin:0;font:400 23px Georgia,serif}.description{min-height:44px;margin:9px 0 16px;color:#76685e;font-size:13px;line-height:1.6;white-space:pre-line}.product-foot{display:flex;align-items:end;justify-content:space-between;gap:10px;padding-top:14px;border-top:1px solid #eadfd1}.price{color:#684a34;font-size:17px;font-weight:700}.availability{color:#5b714e;font-size:12px;text-align:right}.availability-out{color:#9d4939}.empty{padding:56px 18px;border:1px solid #dfd0bc;background:#fffdf9;color:#76685e;text-align:center}.pagination{margin-top:24px}.pagination nav{display:flex;justify-content:space-between}@media(max-width:850px){.grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:560px){.topbar{align-items:flex-start;flex-direction:column}.intro{padding:42px 20px 34px}.catalog{padding:28px 16px 48px}.catalog-head{align-items:flex-start;flex-direction:column}.grid{grid-template-columns:1fr}.product-photo{aspect-ratio:16/10}}
    </style>
    <style>
        .order-nav-form{display:inline}.product-action{display:block;margin-top:16px;padding:11px 14px;background:#654733;color:#fffaf3;text-align:center;text-decoration:none;font-size:13px;font-weight:700}.product-action:hover{background:#503727}
    </style>
    @include('partials.responsive-polish')
</head>
<body>
    <header class="topbar"><a class="brand" href="{{ url('/') }}">Honey's Bite</a><nav>@auth<a href="{{ route('orders.index') }}">Pesanan saya</a><form class="order-nav-form" method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Keluar</button></form>@else<a href="{{ route('login') }}">Masuk</a><a href="{{ route('register') }}">Daftar</a>@endauth</nav></header>
    <section class="intro"><p class="eyebrow">Dari dapur kami</p><h1>Roti yang dibuat dengan rasa.</h1><p class="intro-copy">Temukan pilihan roti dan kudapan segar Honey's Bite, dibuat dengan perhatian untuk menemani hari Anda.</p></section>
    <main class="catalog"><div class="catalog-head"><h2>Pilihan hari ini</h2><span class="count">{{ $products->total() }} produk tersedia</span></div>
        @if ($products->isEmpty())
            <div class="empty">Belum ada produk yang tersedia. Silakan kembali lagi nanti.</div>
        @else
            <div class="grid">
                @foreach ($products as $product)
                    <article class="product">
                        @if ($product->photo_path)<img class="product-photo" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->photo_path) }}" alt="Foto {{ $product->name }}">@else<div class="product-photo photo-empty" aria-hidden="true">Honey's Bite</div>@endif
                        <div class="product-body"><h3>{{ $product->name }}</h3><a class="product-details" href="{{ route('products.show', $product) }}">Detail produk dan ulasan</a><p class="description">{{ $product->description ?: 'Dibuat segar dengan perhatian dari dapur kami.' }}</p><div class="product-foot"><span class="price">Rp{{ number_format((float) $product->price, 0, ',', '.') }}</span><span class="availability {{ $product->stock === 0 ? 'availability-out' : '' }}">{{ $product->stock > 0 ? 'Tersedia · '.$product->stock.' stok' : 'Stok habis' }}</span></div>@if ($product->stock > 0)<a class="product-action" href="{{ route('checkout.create', $product) }}">Pesan produk</a>@endif</div>
                    </article>
                @endforeach
            </div>
            <div class="pagination">{{ $products->links() }}</div>
        @endif
    </main>
</body>
</html>
