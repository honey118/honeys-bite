<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Produk | Honey's Bite</title>
    <style>
        :root{font-family:"Instrument Sans","Segoe UI",sans-serif;color:#38291f;background:#f5eee3}*{box-sizing:border-box}body{margin:0;min-width:320px}.topbar{padding:18px clamp(18px,5vw,64px);background:#543b2d;color:#fff8ee;font:600 23px Georgia,serif}.page{width:min(780px,100%);margin:0 auto;padding:38px 24px 64px}.eyebrow{margin:0 0 8px;color:#8b674d;font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}h1{margin:0 0 28px;font:400 38px Georgia,serif}.form-shell{padding:clamp(22px,5vw,40px);border:1px solid #dfd0bc;background:#fffdf9}.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}.form-field{min-width:0}.form-field-wide{grid-column:1/-1}label{display:block;margin-bottom:8px;color:#49382d;font-size:13px;font-weight:650}input,textarea,select{width:100%;min-height:44px;padding:11px 12px;border:1px solid #d9cbbb;border-radius:3px;background:#fffefa;color:#35271f;font:inherit;font-size:14px}textarea{resize:vertical}.field-hint{display:block;margin-top:7px;color:#76685e;font-size:12px}.field-error{margin:7px 0 0;color:#9d382e;font-size:12px}.button{display:inline-flex;align-items:center;justify-content:center;min-height:44px;margin-top:26px;padding:0 18px;border:0;border-radius:3px;background:#654733;color:#fffaf3;text-decoration:none;font-family:inherit;font-size:13px;font-weight:700;cursor:pointer}.text-link{margin-left:14px;color:#654733;font-size:13px;text-underline-offset:3px}@media(max-width:560px){.page{padding:28px 16px 48px}.form-grid{grid-template-columns:1fr}.form-field-wide{grid-column:auto}h1{font-size:32px}}
    </style>
    @include('partials.responsive-polish')
</head>
<body>
    <header class="topbar"><a class="brand" href="{{ route('admin.home') }}">Honey's Bite · Admin</a><nav aria-label="Navigasi Admin"><a href="{{ route('admin.orders.index') }}">Pesanan</a><a href="{{ route('admin.products.index') }}">Produk</a><a href="{{ route('admin.settings.delivery-fee.edit') }}">Pengaturan ongkir</a></nav></header>
    <main class="page"><p class="eyebrow">Manajemen produk</p><h1>Tambah produk</h1><section class="form-shell"><form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">@csrf @include('admin.products._form')</form></section></main>
</body>
</html>
