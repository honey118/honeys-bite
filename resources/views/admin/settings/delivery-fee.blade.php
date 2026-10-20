<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengaturan Ongkir | Honey's Bite Admin</title>
    <style>
        :root{font-family:"Instrument Sans","Segoe UI",sans-serif;color:#38291f;background:#f5eee3}*{box-sizing:border-box}body{margin:0;min-width:320px}.topbar{display:flex;justify-content:space-between;gap:18px;padding:18px clamp(18px,5vw,64px);background:#543b2d;color:#fff8ee}.brand{font:600 23px Georgia,serif;color:inherit;text-decoration:none}.topbar nav{display:flex;gap:10px;align-items:center}.topbar a:not(.brand){color:#f1dec8;text-underline-offset:4px}.page{width:min(760px,100%);margin:0 auto;padding:36px 24px 64px}.eyebrow{margin:0 0 8px;color:#8b674d;font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}h1{margin:0 0 24px;font:400 38px Georgia,serif}.panel{padding:clamp(20px,4vw,32px);border:1px solid #dfd0bc;background:#fffdf9}.panel p{color:#76685e;font-size:14px;line-height:1.6}.field{max-width:420px;margin-top:24px}label{display:block;margin-bottom:8px;color:#49382d;font-size:13px;font-weight:650}input{width:100%;min-height:46px;padding:11px 12px;border:1px solid #d9cbbb;border-radius:3px;background:#fffefa;color:#35271f;font:inherit;font-size:15px}.hint{display:block;margin-top:7px;color:#76685e;font-size:12px}.error{margin:7px 0 0;color:#9d382e;font-size:12px}.flash{margin-bottom:18px;padding:12px 14px;border:1px solid #bdd0b0;background:#f1f7ed;color:#385332}.button{display:inline-flex;align-items:center;justify-content:center;min-height:44px;margin-top:24px;padding:0 17px;border:0;border-radius:3px;background:#654733;color:#fffaf3;font:inherit;font-size:13px;font-weight:700;cursor:pointer}.button:hover{background:#503727}@media(max-width:520px){.topbar{align-items:flex-start;flex-direction:column}.page{padding:28px 16px 48px}h1{font-size:32px}}
    </style>
    @include('partials.responsive-polish')
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ route('admin.orders.index') }}">Honey's Bite · Admin</a>
        <nav aria-label="Navigasi Admin">
            <a href="{{ route('admin.orders.index') }}">Pesanan</a>
            <a href="{{ route('admin.products.index') }}">Produk</a>
        </nav>
    </header>
    <main class="page">
        <p class="eyebrow">Pengaturan toko</p>
        <h1>Ongkir delivery</h1>
        @if (session('success'))<p class="flash" role="status">{{ session('success') }}</p>@endif
        <section class="panel">
            <p>Atur biaya tetap untuk pesanan delivery. Pesanan pickup tidak dikenakan ongkir. Perubahan hanya berlaku untuk pesanan baru.</p>
            <p>Ongkir saat ini: <strong>Rp{{ \App\Support\Money::format($deliveryFee) }}</strong></p>
            <form method="POST" action="{{ route('admin.settings.delivery-fee.update') }}">
                @csrf
                @method('PUT')
                <div class="field">
                    <label for="delivery_fee">Nominal ongkir (Rp)</label>
                    <input id="delivery_fee" name="delivery_fee" type="number" min="0" max="99999999.99" step="0.01" value="{{ old('delivery_fee', $deliveryFee) }}" required>
                    <span class="hint">Masukkan angka 0 sampai 99.999.999,99. Nilai awal Rp5.000.</span>
                    @error('delivery_fee')<p class="error" role="alert">{{ $message }}</p>@enderror
                </div>
                <button class="button" type="submit">Simpan pengaturan</button>
            </form>
        </section>
    </main>
</body>
</html>
