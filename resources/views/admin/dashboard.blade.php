<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Admin | Honey's Bite</title>
    <style>
        :root{font-family:"Instrument Sans","Segoe UI",sans-serif;color:#38291f;background:#f5eee3;font-synthesis:none;text-rendering:optimizeLegibility}*{box-sizing:border-box}body{margin:0;min-width:320px}.topbar{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:18px clamp(18px,5vw,64px);background:#543b2d;color:#fff8ee}.brand{font:600 23px Georgia,serif;color:inherit;text-decoration:none}.topbar nav{display:flex;flex-wrap:wrap;gap:18px}.topbar nav a{color:#f1dec8;font-size:13px;text-underline-offset:4px}.page{width:min(1120px,100%);margin:0 auto;padding:38px 24px 64px}.eyebrow{margin:0 0 8px;color:#8b674d;font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}h1{margin:0;font:400 clamp(32px,5vw,40px) Georgia,serif}.intro{margin:10px 0 28px;color:#76685e;font-size:14px}.stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.card{min-height:142px;padding:22px;border:1px solid #dfd0bc;background:#fffdf9}.label{margin:0;color:#76685e;font-size:13px}.value{margin:16px 0 0;color:#62452f;font:400 clamp(28px,4vw,36px) Georgia,serif}.sales{font-size:clamp(24px,3.5vw,32px)}.note{margin:9px 0 0;color:#8b674d;font-size:12px}.empty{margin-top:20px;padding:16px;border:1px solid #dfd0bc;background:#fffdf9;color:#76685e;font-size:13px}@media(max-width:760px){.stats{grid-template-columns:repeat(2,minmax(0,1fr))}.topbar{align-items:flex-start;flex-direction:column}}@media(max-width:480px){.page{padding:28px 16px 48px}.stats{grid-template-columns:1fr}.card{min-height:122px}.value{margin-top:10px}}
    </style>
    @include('partials.responsive-polish')
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ route('admin.home') }}">Honey's Bite · Admin</a>
        <nav aria-label="Navigasi Admin">
            <a href="{{ route('admin.orders.index') }}">Pesanan</a>
            <a href="{{ route('admin.products.index') }}">Produk</a>
            <a href="{{ route('admin.settings.delivery-fee.edit') }}">Pengaturan ongkir</a>
        </nav>
    </header>
    <main class="page">
        <p class="eyebrow">Ringkasan toko</p>
        <h1>Dashboard Admin</h1>
        <p class="intro">Statistik operasional Honey's Bite berdasarkan data terbaru.</p>
        <section class="stats" aria-label="Statistik toko">
            <article class="card">
                <p class="label">Total produk aktif</p>
                <p class="value">{{ number_format($stats['active_products']) }}</p>
            </article>
            <article class="card">
                <p class="label">Total pesanan</p>
                <p class="value">{{ number_format($stats['total_orders']) }}</p>
            </article>
            <article class="card">
                <p class="label">Pesanan perlu ditangani</p>
                <p class="value">{{ number_format($stats['pending_orders']) }}</p>
            </article>
            <article class="card">
                <p class="label">Total penjualan aktual</p>
                <p class="value sales">Rp{{ \App\Support\Money::format($stats['paid_sales']) }}</p>
                <p class="note">Hanya pesanan berstatus pembayaran “Dibayar”.</p>
            </article>
            <article class="card">
                <p class="label">Produk aktif dengan stok menipis</p>
                <p class="value">{{ number_format($stats['low_stock_products']) }}</p>
                <p class="note">Stok 5 atau kurang, termasuk stok habis.</p>
            </article>
        </section>
    </main>
</body>
</html>
