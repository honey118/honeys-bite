<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Produk | Honey's Bite Admin</title>
    <style>
        :root{font-family:"Instrument Sans","Segoe UI",sans-serif;color:#38291f;background:#f5eee3;font-synthesis:none;text-rendering:optimizeLegibility}*{box-sizing:border-box}body{margin:0;min-width:320px}.topbar{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:18px clamp(18px,5vw,64px);background:#543b2d;color:#fff8ee}.brand{font:600 23px Georgia,serif}.topbar a{color:#f1dec8;text-underline-offset:4px}.page{width:min(1120px,100%);margin:0 auto;padding:36px 24px 64px}.page-head{display:flex;align-items:end;justify-content:space-between;gap:20px;margin-bottom:24px}.eyebrow{margin:0 0 8px;color:#8b674d;font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}h1{margin:0;font:400 38px Georgia,serif}.button{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:0 16px;border:0;border-radius:3px;background:#654733;color:#fffaf3;text-decoration:none;font-family:inherit;font-size:13px;font-weight:700;cursor:pointer}.button:hover{background:#503727}.button-secondary{background:#eee2d1;color:#543b2d}.flash{margin-bottom:18px;padding:12px 14px;border:1px solid #bdd0b0;background:#f1f7ed;color:#385332}.table-wrap{overflow-x:auto;border:1px solid #dfd0bc;background:#fffdf9}table{width:100%;border-collapse:collapse;text-align:left}th,td{padding:14px 16px;border-bottom:1px solid #eadfD1;vertical-align:middle}th{color:#786455;font-size:11px;letter-spacing:.08em;text-transform:uppercase}td{font-size:14px}.product-cell{display:flex;align-items:center;gap:12px;min-width:210px}.thumb{width:54px;height:54px;flex:none;object-fit:cover;background:#eee2d1}.thumb-empty{display:grid;place-items:center;color:#8b674d;font-size:10px}.status{display:inline-block;padding:5px 8px;background:#edf3e8;color:#42623a;font-size:11px;font-weight:700}.status-archived{background:#f2e8dc;color:#805c40}.actions{display:flex;align-items:center;gap:12px;white-space:nowrap}.text-link{color:#654733;font-weight:650;text-underline-offset:3px}.inline-form{display:inline}.archive-button{padding:0;border:0;background:none;color:#9b3f32;font:inherit;font-size:13px;text-decoration:underline;text-underline-offset:3px;cursor:pointer}.empty{padding:38px;text-align:center;color:#76685e}.pagination{margin-top:20px}.pagination nav{display:flex;justify-content:space-between}@media(max-width:640px){.topbar{align-items:flex-start;flex-direction:column}.page{padding:28px 16px 48px}.page-head{align-items:flex-start;flex-direction:column}h1{font-size:32px}table{min-width:680px}}
    </style>
    @include('partials.responsive-polish')
</head>
<body>
    <header class="topbar"><a class="brand" href="{{ route('admin.orders.index') }}">Honey's Bite · Admin</a><nav aria-label="Navigasi Admin"><a href="{{ route('admin.settings.delivery-fee.edit') }}">Pengaturan ongkir</a> · <a href="{{ route('catalog.index') }}">Lihat katalog pelanggan</a></nav></header>
    <main class="page">
        <div class="page-head"><div><p class="eyebrow">Ruang admin</p><h1>Produk</h1></div><a class="button" href="{{ route('admin.products.create') }}">Tambah produk</a></div>
        @if (session('success'))<p class="flash" role="status">{{ session('success') }}</p>@endif
        <div class="table-wrap" tabindex="0" role="region" aria-label="Daftar produk, dapat digulir horizontal">
            <table>
                <thead><tr><th>Produk</th><th>Harga</th><th>Stok</th><th>Status</th><th>Tindakan</th></tr></thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td><div class="product-cell">@if ($product->photo_path)<img class="thumb" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->photo_path) }}" alt="Foto {{ $product->name }}">@else<span class="thumb thumb-empty" aria-hidden="true">Tanpa foto</span>@endif<strong>{{ $product->name }}</strong></div></td>
                            <td>Rp{{ number_format((float) $product->price, 0, ',', '.') }}</td>
                            <td>{{ $product->stock }}</td>
                            <td><span class="status {{ $product->is_active ? '' : 'status-archived' }}">{{ $product->is_active ? 'Aktif' : 'Diarsipkan' }}</span></td>
                            <td><div class="actions"><a class="text-link" href="{{ route('admin.products.show', $product) }}">Detail</a><a class="text-link" href="{{ route('admin.products.edit', $product) }}">Edit</a>@if ($product->is_active)<form class="inline-form" method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Arsipkan produk ini?')">@csrf @method('DELETE')<button class="archive-button" type="submit">Arsipkan</button></form>@endif</div></td>
                        </tr>
                    @empty
                        <tr><td class="empty" colspan="5">Belum ada produk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $products->links() }}</div>
    </main>
</body>
</html>
