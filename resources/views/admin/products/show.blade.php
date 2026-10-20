<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $product->name }} | Honey's Bite</title>
    <style>
        :root{font-family:"Instrument Sans","Segoe UI",sans-serif;color:#38291f;background:#f5eee3}*{box-sizing:border-box}body{margin:0;min-width:320px}.topbar{display:flex;justify-content:space-between;gap:16px;padding:18px clamp(18px,5vw,64px);background:#543b2d;color:#fff8ee}.brand{font:600 23px Georgia,serif}.topbar a{color:#f1dec8;text-underline-offset:4px}.page{width:min(900px,100%);margin:0 auto;padding:38px 24px 64px}.eyebrow{margin:0 0 8px;color:#8b674d;font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}h1{margin:0;font:400 38px Georgia,serif}.detail{display:grid;grid-template-columns:minmax(220px,.8fr) 1fr;gap:32px;margin-top:28px;padding:clamp(20px,4vw,36px);border:1px solid #dfd0bc;background:#fffdf9}.photo{width:100%;aspect-ratio:1;object-fit:cover;background:#eee2d1}.photo-empty{display:grid;place-items:center;color:#8b674d;font-size:13px}.price{margin:20px 0 8px;color:#684a34;font:400 28px Georgia,serif}.description{color:#65574d;font-size:14px;line-height:1.7;white-space:pre-line}.meta{display:flex;flex-wrap:wrap;gap:12px;margin-top:22px}.tag{padding:7px 10px;background:#f2e8dc;color:#624932;font-size:12px}.button{display:inline-flex;align-items:center;justify-content:center;min-height:42px;margin-top:24px;padding:0 16px;border:0;border-radius:3px;background:#654733;color:#fffaf3;text-decoration:none;font-size:13px;font-weight:700}.text-link{margin-left:14px;color:#654733;font-size:13px;text-underline-offset:3px}@media(max-width:650px){.page{padding:28px 16px 48px}.detail{grid-template-columns:1fr;gap:20px}.photo{max-height:420px}h1{font-size:32px}}
    </style>
    @include('partials.responsive-polish')
</head>
<body>
    <header class="topbar"><a class="brand" href="{{ route('admin.home') }}">Honey's Bite · Admin</a><nav aria-label="Navigasi Admin"><a href="{{ route('admin.orders.index') }}">Pesanan</a><a href="{{ route('admin.products.index') }}">Produk</a><a href="{{ route('admin.settings.delivery-fee.edit') }}">Pengaturan ongkir</a><a href="{{ route('catalog.index') }}">Katalog pelanggan</a></nav></header>
    <main class="page"><p class="eyebrow">Detail produk</p><h1>{{ $product->name }}</h1>
        <section class="detail">
            @if ($product->photo_path)<img class="photo" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->photo_path) }}" alt="Foto {{ $product->name }}">@else<div class="photo photo-empty" aria-hidden="true">Belum ada foto</div>@endif
            <div><p class="price">Rp{{ number_format((float) $product->price, 0, ',', '.') }}</p><p class="description">{{ $product->description ?: 'Belum ada deskripsi.' }}</p><div class="meta"><span class="tag">Stok: {{ $product->stock }}</span><span class="tag">{{ $product->is_active ? 'Aktif' : 'Diarsipkan' }}</span></div><a class="button" href="{{ route('admin.products.edit', $product) }}">Edit produk</a><a class="text-link" href="{{ route('admin.products.index') }}">Kembali ke daftar</a></div>
        </section>
    </main>
</body>
</html>
