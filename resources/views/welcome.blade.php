<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ config('app.name', "Honey's Bite") }}</title>
    <style>
        :root{font-family:"Instrument Sans","Segoe UI",sans-serif;color:#38291f;background:#f5eee3;font-synthesis:none;text-rendering:optimizeLegibility}*{box-sizing:border-box}body{min-width:320px;min-height:100vh;margin:0}.topbar{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:20px clamp(18px,6vw,78px);background:#543b2d;color:#fff8ee}.brand{color:inherit;text-decoration:none;font:600 26px Georgia,serif}.topbar nav{display:flex;flex-wrap:wrap;align-items:center;gap:18px}.topbar nav a{min-height:44px;display:inline-flex;align-items:center;color:#f1dec8;font-size:13px;text-underline-offset:4px}.hero{display:grid;min-height:min(680px,calc(100vh - 80px));place-items:center;padding:64px 20px;background:radial-gradient(ellipse at 80% 20%,#f8efdf 0,transparent 38%),#eee2d1}.hero-content{width:min(760px,100%);text-align:center}.eyebrow{margin:0 0 16px;color:#8b674d;font-size:12px;font-weight:700;letter-spacing:.16em;text-transform:uppercase}.hero h1{max-width:700px;margin:0 auto;color:#543b2d;font:400 clamp(38px,8vw,76px)/1.08 Georgia,serif}.copy{max-width:560px;margin:22px auto 0;color:#716155;font-size:16px;line-height:1.75}.actions{display:flex;flex-wrap:wrap;justify-content:center;gap:12px;margin-top:30px}.button{display:inline-flex;min-height:48px;align-items:center;justify-content:center;padding:0 20px;border:1px solid #654733;background:#654733;color:#fffaf3;text-decoration:none;font-size:14px;font-weight:700}.button-secondary{background:transparent;color:#654733}.flash{width:min(600px,100%);margin:0 auto 20px;padding:12px 16px;border:1px solid #bdd0b0;background:#f1f7ed;color:#385332;text-align:left}@media(max-width:600px){.topbar{align-items:flex-start;flex-direction:column;padding:16px}.topbar nav{gap:10px}.hero{min-height:calc(100vh - 120px);padding:48px 16px}.copy{font-size:15px}.actions{align-items:stretch;flex-direction:column}.button{width:100%}}
    </style>
    @include('partials.responsive-polish')
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ route('catalog.index') }}">Honey's Bite</a>
        <nav aria-label="Navigasi utama">
            <a href="{{ route('catalog.index') }}">Katalog</a>
            @auth
                <a href="{{ route('orders.index') }}">Pesanan saya</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Keluar</button></form>
            @else
                <a href="{{ route('login') }}">Masuk</a>
                <a href="{{ route('register') }}">Daftar</a>
            @endauth
        </nav>
    </header>
    <main class="hero">
        <section class="hero-content" aria-labelledby="welcome-title">
            @if (session('success'))<p class="flash" role="status">{{ session('success') }}</p>@endif
            <p class="eyebrow">Hangat dari dapur kami</p>
            <h1 id="welcome-title">Roti yang dibuat dengan rasa.</h1>
            <p class="copy">Temukan roti dan kudapan segar Honey's Bite, dibuat dengan perhatian untuk menemani hari Anda.</p>
            <div class="actions">
                <a class="button" href="{{ route('catalog.index') }}">Jelajahi katalog</a>
                @guest<a class="button button-secondary" href="{{ route('register') }}">Buat akun pelanggan</a>@endguest
            </div>
        </section>
    </main>
</body>
</html>
