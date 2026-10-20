<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesanan Admin | Honey's Bite</title>
    <style>
        :root{font-family:"Instrument Sans","Segoe UI",sans-serif;color:#38291f;background:#f5eee3}*{box-sizing:border-box}body{margin:0;min-width:320px}.topbar{display:flex;justify-content:space-between;gap:18px;padding:18px clamp(18px,5vw,64px);background:#543b2d;color:#fff8ee}.brand{font:600 23px Georgia,serif;color:inherit;text-decoration:none}.topbar a:not(.brand){color:#f1dec8;text-underline-offset:4px}.page{width:min(1180px,100%);margin:0 auto;padding:36px 24px 64px}.eyebrow{margin:0 0 8px;color:#8b674d;font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}h1{margin:0 0 24px;font:400 38px Georgia,serif}.flash{margin-bottom:16px;padding:12px 14px;border:1px solid #bdd0b0;background:#f1f7ed;color:#385332}.table-wrap{overflow-x:auto;border:1px solid #dfd0bc;background:#fffdf9}table{width:100%;border-collapse:collapse;text-align:left}th,td{padding:14px 16px;border-bottom:1px solid #eadfd1;vertical-align:top}th{color:#786455;font-size:11px;letter-spacing:.08em;text-transform:uppercase}td{font-size:13px}.muted{margin-top:5px;color:#76685e;font-size:12px}.link{color:#654733;font-weight:700;text-underline-offset:3px}.status{display:inline-block;padding:6px 9px;background:#f2e8dc;color:#805c40;font-size:12px}.empty{text-align:center;color:#76685e;padding:36px}.pagination{margin-top:20px}.pagination nav{display:flex;justify-content:space-between}@media(max-width:700px){.page{padding:28px 16px 48px}table{min-width:850px}h1{font-size:32px}}
    </style>
    @include('partials.responsive-polish')
</head>
<body>
    <header class="topbar"><a class="brand" href="{{ route('admin.home') }}">Honey's Bite · Admin</a><nav aria-label="Navigasi Admin"><a href="{{ route('admin.products.index') }}">Manajemen produk</a> · <a href="{{ route('admin.settings.delivery-fee.edit') }}">Pengaturan ongkir</a></nav></header>
    <main class="page"><p class="eyebrow">Pemenuhan pesanan</p><h1>Pesanan pelanggan</h1>
        @if (session('success'))<p class="flash" role="status">{{ session('success') }}</p>@endif
        <div class="table-wrap" tabindex="0" role="region" aria-label="Daftar pesanan, dapat digulir horizontal"><table><thead><tr><th>Nomor / Pelanggan</th><th>Produk</th><th>Pemenuhan / Pembayaran</th><th>Total</th><th>Status pesanan</th><th>Detail</th></tr></thead><tbody>
            @forelse ($orders as $order)
                <tr><td><strong>{{ $order->order_number }}</strong><div class="muted">{{ $order->customer->name }} · {{ $order->customer->email }}</div></td><td>{{ $order->item->product_name }}<div class="muted">{{ $order->item->quantity }} unit</div></td><td>{{ $order->fulfillment_method === 'pickup' ? 'Pickup' : 'Delivery' }}<div class="muted">{{ strtoupper($order->payment_method) }} · <span class="status status-payment">{{ $order->payment_status }}</span></div></td><td>Rp{{ \App\Support\Money::format($order->total) }}</td><td><span class="status status-order">{{ $order->status }}</span></td><td><a class="link" href="{{ route('admin.orders.show', $order) }}">Buka</a></td></tr>
            @empty
                <tr><td class="empty" colspan="6">Belum ada pesanan.</td></tr>
            @endforelse
        </tbody></table></div>
        <div class="pagination">{{ $orders->links() }}</div>
    </main>
</body>
</html>
