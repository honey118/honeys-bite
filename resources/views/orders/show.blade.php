<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesanan {{ $order->order_number }} | Honey's Bite</title>
    <style>
        :root{font-family:"Instrument Sans","Segoe UI",sans-serif;color:#38291f;background:#f5eee3}*{box-sizing:border-box}body{margin:0;min-width:320px}.topbar{display:flex;justify-content:space-between;gap:18px;padding:18px clamp(18px,6vw,78px);background:#543b2d;color:#fff8ee}.brand{font:600 24px Georgia,serif;color:inherit;text-decoration:none}.topbar a:not(.brand){color:#f1dec8;text-underline-offset:4px}.page{width:min(860px,100%);margin:0 auto;padding:38px 24px 64px}.eyebrow{margin:0 0 8px;color:#8b674d;font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}h1{margin:0;font:400 36px Georgia,serif}.intro{margin:9px 0 24px;color:#76685e;font-size:13px}.confirmation{margin-bottom:20px;padding:16px 18px;border:1px solid #bdd0b0;background:#f1f7ed;color:#385332}.confirmation strong{display:block;margin-bottom:4px}.panel{margin-top:16px;padding:clamp(20px,4vw,30px);border:1px solid #dfd0bc;background:#fffdf9}.panel h2{margin:0 0 16px;font:400 23px Georgia,serif}.item-row,.total-row{display:flex;justify-content:space-between;gap:18px;padding:11px 0;border-bottom:1px solid #eadfd1;font-size:14px}.item-row:last-child{border-bottom:0}.item-name{font-weight:650}.muted{margin-top:5px;color:#76685e;font-size:12px}.totals{width:min(360px,100%);margin:14px 0 0 auto}.total-row{font-size:13px}.total-final{border:0;color:#654733;font-size:17px;font-weight:700}.address{color:#65574d;font-size:14px;line-height:1.65;white-space:pre-line}.meta{display:flex;flex-wrap:wrap;gap:12px;margin-top:18px}.tag{padding:7px 10px;background:#f2e8dc;color:#624932;font-size:12px}.link{display:inline-block;margin-top:22px;color:#654733;font-size:13px;font-weight:700;text-underline-offset:3px}@media(max-width:520px){.page{padding:28px 16px 48px}h1{font-size:30px}.item-row{align-items:flex-start;flex-direction:column;gap:4px}}
        .review-form{display:grid;gap:18px}.review-form .field{min-width:0}.review-form label{display:block;margin-bottom:8px;color:#49382d;font-size:13px;font-weight:650}.review-form select,.review-form textarea{display:block;width:100%;min-height:44px;padding:11px 12px;border:1px solid #d9cbbb;border-radius:3px;background:#fffefa;color:#35271f;font:inherit;font-size:14px}.review-form textarea{min-height:120px;resize:vertical}.review-form select:focus-visible,.review-form textarea:focus-visible{border-color:#836044;outline:3px solid rgb(131 96 68 / 20%);outline-offset:2px}.review-submit{display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:0 18px;border:0;border-radius:3px;background:#654733;color:#fffaf3;font:inherit;font-size:13px;font-weight:700;cursor:pointer}.review-submit:hover{background:#503727}.review-submit:focus-visible{outline:3px solid #8b5e36;outline-offset:3px}
    </style>
    @include('partials.responsive-polish')
</head>
<body>
    <header class="topbar"><a class="brand" href="{{ route('catalog.index') }}">Honey's Bite</a><a href="{{ route('orders.index') }}">Pesanan saya</a></header>
    <main class="page"><p class="eyebrow">Detail pesanan</p><h1>{{ $order->order_number }}</h1><p class="intro">Dibuat {{ $order->created_at->format('d M Y, H:i') }}</p>
        @if (session('order_created'))<div class="confirmation" role="status"><strong>Pesanan berhasil dibuat.</strong>Simpan nomor pesanan ini untuk melihat status dan detailnya.</div>@endif
        @if (session('success'))<div class="confirmation" role="status">{{ session('success') }}</div>@endif
        @if ($errors->has('review') && ! $canReviewProduct)<p class="address" role="alert">{{ $errors->first('review') }}</p>@endif
        <section class="panel"><h2>Rincian item</h2><div class="item-row"><div><span class="item-name">{{ $order->item->product_name }}</span><div class="muted">{{ $order->item->quantity }} × Rp{{ \App\Support\Money::format($order->item->unit_price) }}</div></div><strong>Rp{{ \App\Support\Money::format($order->item->subtotal) }}</strong></div><div class="totals"><div class="total-row"><span>Subtotal</span><span>Rp{{ \App\Support\Money::format($order->subtotal) }}</span></div><div class="total-row"><span>Biaya delivery</span><span>Rp{{ \App\Support\Money::format($order->delivery_fee) }}</span></div><div class="total-row total-final"><span>Total</span><span>Rp{{ \App\Support\Money::format($order->total) }}</span></div></div></section>
        @if ($order->item->product)
            <p><a class="link" href="{{ route('products.show', $order->item->product) }}">Lihat produk dan ulasannya</a></p>
        @endif
        <section class="panel"><h2>Pemenuhan dan status</h2><div class="meta"><span class="tag">{{ $order->fulfillment_method === 'pickup' ? 'Ambil sendiri' : 'Delivery' }}</span><span class="tag tag-order">Pesanan: {{ $order->status }}</span><span class="tag tag-payment">Pembayaran: {{ $order->payment_status }}</span></div>@if ($order->fulfillment_method === 'delivery')<p class="address"><strong>{{ $order->recipient_name }}</strong><br>{{ $order->recipient_phone }}<br>{{ $order->delivery_address }}</p>@endif @if ($order->rejection_reason)<p class="address"><strong>Alasan penolakan:</strong><br>{{ $order->rejection_reason }}</p>@endif</section>
        @if ($canReviewProduct)
            <section class="panel">
                <h2>Beri rating dan ulasan</h2>
                @error('review')<p class="address" role="alert">{{ $message }}</p>@enderror
                <form class="review-form" method="POST" action="{{ route('products.reviews.store', $order->item->product) }}">
                    @csrf
                    <div class="field">
                        <label for="rating">Rating</label>
                        <select id="rating" name="rating" required>
                            <option value="">Pilih rating</option>
                            @for ($rating = 1; $rating <= 5; $rating++)
                                <option value="{{ $rating }}" @selected(old('rating') == $rating)>{{ $rating }} dari 5</option>
                            @endfor
                        </select>
                        @error('rating')<p class="address" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label for="body">Ulasan</label>
                        <textarea id="body" name="body" maxlength="1000" required>{{ old('body') }}</textarea>
                        @error('body')<p class="address" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <button class="review-submit" type="submit">Kirim ulasan</button>
                </form>
            </section>
        @elseif ($order->status === \App\Models\Order::STATUS_COMPLETED && $order->item->product)
            <section class="panel"><p>Anda sudah memberikan ulasan untuk produk ini.</p></section>
        @endif
        @if ($order->status === \App\Models\Order::STATUS_AWAITING_PAYMENT && in_array($order->payment_method, ['bank_transfer', 'qris_demo'], true) && $order->payment_status === \App\Models\Order::PAYMENT_UNPAID)
            <section class="panel"><h2>Instruksi pembayaran demo</h2><p class="address"><strong>SIMULASI DEMO - TIDAK ADA TRANSAKSI UANG.</strong><br>{{ $order->payment_method === 'bank_transfer' ? 'Ini adalah contoh instruksi transfer bank. Jangan mengirim uang atau memasukkan data rekening.' : 'Ini adalah tampilan simulasi QRIS. Tidak tersedia QR pembayaran sungguhan.' }}</p><form method="POST" action="{{ route('orders.simulate-payment', $order) }}">@csrf<button class="button" type="submit">Simulasikan pembayaran berhasil</button></form></section>
        @endif
        <a class="link" href="{{ route('orders.index') }}">Kembali ke riwayat pesanan</a>
    </main>
</body>
</html>
