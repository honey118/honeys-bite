<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesanan Saya | Honey's Bite</title>
    <style>
        :root{font-family:"Instrument Sans","Segoe UI",sans-serif;color:#38291f;background:#f5eee3}*{box-sizing:border-box}body{margin:0;min-width:320px}.topbar{display:flex;justify-content:space-between;gap:18px;padding:18px clamp(18px,6vw,78px);background:#543b2d;color:#fff8ee}.brand{font:600 24px Georgia,serif;color:inherit;text-decoration:none}.topbar a:not(.brand){color:#f1dec8;text-underline-offset:4px}.page{width:min(1000px,100%);margin:0 auto;padding:38px 24px 64px}.eyebrow{margin:0 0 8px;color:#8b674d;font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}h1{margin:0 0 26px;font:400 38px Georgia,serif}.flash{margin-bottom:18px;padding:14px 16px;border:1px solid #bdd0b0;background:#f1f7ed;color:#385332}.flash strong{display:block;margin-bottom:4px}.order-list{display:grid;gap:12px}.order{display:grid;grid-template-columns:1.1fr 1fr 1fr auto;align-items:center;gap:16px;padding:18px 20px;border:1px solid #dfd0bc;background:#fffdf9}.order-number{color:#654733;font-weight:700}.muted{margin-top:5px;color:#76685e;font-size:12px}.order-total{font-weight:700}.status{display:inline-block;padding:6px 9px;background:#f2e8dc;color:#805c40;font-size:12px}.link{color:#654733;font-size:13px;font-weight:700;text-underline-offset:3px}.empty{padding:42px 20px;border:1px solid #dfd0bc;background:#fffdf9;color:#76685e;text-align:center}.empty a{color:#654733}.pagination{margin-top:20px}.pagination nav{display:flex;justify-content:space-between}@media(max-width:720px){.page{padding:28px 16px 48px}.order{grid-template-columns:1fr 1fr}.order-number{grid-column:1/-1}h1{font-size:33px}}@media(max-width:420px){.order{grid-template-columns:1fr}.order-number{grid-column:auto}}
    </style>
    @include('partials.responsive-polish')
</head>
<body>
    <header class="topbar"><a class="brand" href="{{ route('catalog.index') }}">Honey's Bite</a><a href="{{ route('catalog.index') }}">Katalog</a></header>
    <main class="page"><p class="eyebrow">Akun pelanggan</p><h1>Pesanan saya</h1>
        @if (session('success'))<div class="flash" role="status">{{ session('success') }}</div>@endif
        @if ($orders->isEmpty())<div class="empty">Anda belum memiliki pesanan. <a href="{{ route('catalog.index') }}">Lihat katalog</a></div>@else
            <div class="order-list">@foreach ($orders as $order)<article class="order"><div><a class="order-number" href="{{ route('orders.show', $order) }}">{{ $order->order_number }}</a><div class="muted">{{ $order->created_at->format('d M Y, H:i') }}</div></div><div>{{ $order->item->product_name }}<div class="muted">{{ $order->item->quantity }} item · {{ ucfirst($order->fulfillment_method) }}</div></div><div><span class="order-total">Rp{{ \App\Support\Money::format($order->total) }}</span><div class="muted"><span class="status status-order">{{ $order->status }}</span></div></div><a class="link" href="{{ route('orders.show', $order) }}">Lihat detail</a></article>@endforeach</div>
            <div class="pagination">{{ $orders->links() }}</div>
        @endif
    </main>
</body>
</html>
