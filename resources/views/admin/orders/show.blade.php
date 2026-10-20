<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $order->order_number }} | Honey's Bite Admin</title>
    <style>
        :root{font-family:"Instrument Sans","Segoe UI",sans-serif;color:#38291f;background:#f5eee3}*{box-sizing:border-box}body{margin:0;min-width:320px}.topbar{display:flex;justify-content:space-between;gap:18px;padding:18px clamp(18px,5vw,64px);background:#543b2d;color:#fff8ee}.brand{font:600 23px Georgia,serif;color:inherit;text-decoration:none}.topbar a:not(.brand){color:#f1dec8;text-underline-offset:4px}.page{width:min(900px,100%);margin:0 auto;padding:36px 24px 64px}.eyebrow{margin:0 0 8px;color:#8b674d;font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}h1{margin:0;font:400 36px Georgia,serif}.flash,.error{margin:18px 0;padding:12px 14px;border:1px solid #bdd0b0;background:#f1f7ed;color:#385332}.error{border-color:#d6a39b;background:#fbefed;color:#843c33}.grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:24px}.panel{padding:22px;border:1px solid #dfd0bc;background:#fffdf9}.panel-wide{grid-column:1/-1}.panel h2{margin:0 0 16px;font:400 22px Georgia,serif}.line{margin:9px 0;color:#57483d;font-size:14px;line-height:1.55}.line strong{color:#392a20}.muted{color:#76685e;font-size:12px}.status{display:inline-block;margin:4px 6px 4px 0;padding:7px 10px;background:#f2e8dc;color:#805c40;font-size:12px}.reason{padding:12px;background:#fbefed;color:#843c33;white-space:pre-line}.actions{display:flex;flex-wrap:wrap;gap:12px}.button{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:0 15px;border:0;border-radius:3px;background:#654733;color:#fffaf3;font:inherit;font-size:13px;font-weight:700;cursor:pointer}.button-danger{background:#923e32}.button:hover{filter:brightness(.92)}label{display:block;margin:0 0 8px;color:#49382d;font-size:13px;font-weight:650}textarea{display:block;width:100%;min-height:90px;padding:11px 12px;border:1px solid #d9cbbb;border-radius:3px;background:#fffefa;color:#35271f;font:inherit;font-size:14px;resize:vertical}.field-error{margin:7px 0 0;color:#9d382e;font-size:12px}.link{display:inline-block;margin-top:20px;color:#654733;font-weight:700;text-underline-offset:3px}@media(max-width:620px){.page{padding:28px 16px 48px}.grid{grid-template-columns:1fr}.panel-wide{grid-column:auto}h1{font-size:30px}}
    </style>
    @include('partials.responsive-polish')
</head>
<body>
    <header class="topbar"><a class="brand" href="{{ route('admin.home') }}">Honey's Bite · Admin</a><nav aria-label="Navigasi Admin"><a href="{{ route('admin.orders.index') }}">Pesanan</a><a href="{{ route('admin.products.index') }}">Produk</a><a href="{{ route('admin.settings.delivery-fee.edit') }}">Pengaturan ongkir</a></nav></header>
    <main class="page"><p class="eyebrow">Detail dan tindakan pesanan</p><h1>{{ $order->order_number }}</h1>
        @if (session('success'))<p class="flash" role="status">{{ session('success') }}</p>@endif
        @if ($errors->any())<p class="error" role="alert">{{ $errors->first() }}</p>@endif
        <div class="grid">
            <section class="panel"><h2>Pelanggan</h2><p class="line"><strong>{{ $order->customer->name }}</strong></p><p class="line">{{ $order->customer->email }}</p><p class="line">{{ $order->recipient_phone ?: 'Kontak tidak dicatat' }}</p></section>
            <section class="panel"><h2>Status</h2><span class="status status-order">Pesanan: {{ $order->status }}</span><span class="status status-payment">Pembayaran: {{ $order->payment_status }}</span><p class="line">Metode: {{ strtoupper($order->payment_method) }}</p><p class="line">Pemenuhan: {{ $order->fulfillment_method === 'pickup' ? 'Pickup' : 'Delivery' }}</p></section>
            <section class="panel"><h2>Item</h2><p class="line"><strong>{{ $order->item->product_name }}</strong></p><p class="line">{{ $order->item->quantity }} × Rp{{ \App\Support\Money::format($order->item->unit_price) }}</p><p class="line">Subtotal item: Rp{{ \App\Support\Money::format($order->item->subtotal) }}</p></section>
            <section class="panel"><h2>Ringkasan</h2><p class="line">Subtotal: Rp{{ \App\Support\Money::format($order->subtotal) }}</p><p class="line">Pengiriman: Rp{{ \App\Support\Money::format($order->delivery_fee) }}</p><p class="line"><strong>Total: Rp{{ \App\Support\Money::format($order->total) }}</strong></p></section>
            @if ($order->fulfillment_method === 'delivery')<section class="panel panel-wide"><h2>Alamat penerima</h2><p class="line"><strong>{{ $order->recipient_name }}</strong></p><p class="line">{{ $order->delivery_address }}</p></section>@endif
            @if ($order->rejection_reason)<section class="panel panel-wide"><h2>Alasan penolakan</h2><p class="reason">{{ $order->rejection_reason }}</p></section>@endif
            @if ($order->status === \App\Models\Order::STATUS_PENDING)
                <section class="panel panel-wide"><h2>Proses pesanan</h2><div class="actions"><form method="POST" action="{{ route('admin.orders.confirm', $order) }}">@csrf<button class="button" type="submit">Konfirmasi pesanan</button></form></div><form method="POST" action="{{ route('admin.orders.reject', $order) }}" style="margin-top:18px">@csrf<label for="rejection_reason">Alasan penolakan</label><textarea id="rejection_reason" name="rejection_reason" required maxlength="2000">{{ old('rejection_reason') }}</textarea>@error('rejection_reason')<p class="field-error">{{ $message }}</p>@enderror<button class="button button-danger" type="submit" style="margin-top:12px">Tolak pesanan</button></form></section>
            @endif
            @if (in_array($order->status, [\App\Models\Order::STATUS_IN_TRANSIT, \App\Models\Order::STATUS_READY_FOR_PICKUP], true))
                <section class="panel panel-wide"><h2>Penyelesaian pesanan</h2><form method="POST" action="{{ route('admin.orders.complete', $order) }}">@csrf<button class="button" type="submit">Tandai pesanan selesai</button></form></section>
            @endif
            @if ($order->payment_method === 'cod' && $order->payment_status === \App\Models\Order::PAYMENT_UNPAID && in_array($order->status, [\App\Models\Order::STATUS_IN_TRANSIT, \App\Models\Order::STATUS_READY_FOR_PICKUP, \App\Models\Order::STATUS_COMPLETED], true))
                <section class="panel panel-wide"><h2>Pembayaran COD</h2><form method="POST" action="{{ route('admin.orders.mark-cod-paid', $order) }}">@csrf<button class="button" type="submit">Tandai pembayaran diterima</button></form></section>
            @endif
        </div>
        <a class="link" href="{{ route('admin.orders.index') }}">Kembali ke daftar pesanan</a>
    </main>
</body>
</html>
