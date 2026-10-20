<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Daftar | Honey's Bite</title>
    <style>
        :root {
            color-scheme: light;
            font-family: "Instrument Sans", "Segoe UI", sans-serif;
            color: #35271f;
            background: #f5eee3;
            font-synthesis: none;
            text-rendering: optimizeLegibility;
        }

        * { box-sizing: border-box; }

        body {
            min-width: 320px;
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 32px;
            background: linear-gradient(135deg, #f5eee3 0%, #eee2d1 100%);
        }

        .auth-layout {
            display: grid;
            grid-template-columns: minmax(260px, 0.85fr) minmax(340px, 1fr);
            width: min(100%, 900px);
            min-height: 650px;
            overflow: hidden;
            border: 1px solid #dfd0bc;
            background: #fffdf9;
            box-shadow: 0 24px 70px rgb(64 42 28 / 12%);
        }

        .brand-panel {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: clamp(32px, 5vw, 58px);
            color: #fff8ee;
            background-color: #543b2d;
            background-image: repeating-linear-gradient(135deg, transparent 0 22px, rgb(255 255 255 / 3%) 22px 23px);
        }

        .brand-name {
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            font-size: 28px;
            font-weight: 600;
        }

        .brand-mark {
            display: inline-block;
            width: 9px;
            height: 9px;
            margin-right: 8px;
            transform: rotate(45deg);
            background: #d9a879;
        }

        .brand-copy { max-width: 290px; }

        .brand-copy p {
            margin: 0 0 14px;
            color: #dfcbb3;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .brand-copy h2 {
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(34px, 4vw, 48px);
            font-weight: 400;
            line-height: 1.12;
        }

        .brand-note {
            margin: 0;
            color: #dfcbb3;
            font-size: 13px;
        }

        .form-panel {
            display: flex;
            align-items: center;
            padding: clamp(30px, 6vw, 68px);
        }

        .form-content { width: 100%; max-width: 390px; margin: 0 auto; }

        .eyebrow {
            margin: 0 0 10px;
            color: #8b674d;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            font-size: 38px;
            font-weight: 400;
            line-height: 1.15;
        }

        .intro { margin: 12px 0 24px; color: #76685e; font-size: 14px; line-height: 1.6; }
        .field { margin-top: 16px; }
        label { display: block; margin-bottom: 8px; color: #49382d; font-size: 13px; font-weight: 650; }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            min-height: 46px;
            padding: 11px 14px;
            border: 1px solid #d9cbbb;
            border-radius: 3px;
            outline: none;
            background: #fffefa;
            color: #35271f;
            font: inherit;
            font-size: 14px;
        }

        input:focus-visible { border-color: #836044; box-shadow: 0 0 0 3px rgb(131 96 68 / 15%); }
        input[aria-invalid="true"] { border-color: #a23d32; }
        .field-error { margin: 7px 0 0; color: #9d382e; font-size: 12px; line-height: 1.45; }

        .submit-button {
            width: 100%;
            min-height: 48px;
            margin-top: 24px;
            border: 0;
            border-radius: 3px;
            background: #654733;
            color: #fffaf3;
            cursor: pointer;
            font: inherit;
            font-size: 14px;
            font-weight: 700;
        }

        .submit-button:hover { background: #503727; }
        .submit-button:focus-visible, a:focus-visible { outline: 3px solid #b17e52; outline-offset: 3px; }
        .switch-copy { margin: 20px 0 0; color: #76685e; text-align: center; font-size: 13px; }
        a { color: #654733; font-weight: 700; text-underline-offset: 3px; }

        @media (max-width: 700px) {
            body { display: block; padding: 16px; }
            .auth-layout { grid-template-columns: 1fr; min-height: 0; margin: 0 auto; }
            .brand-panel { min-height: 190px; padding: 24px; }
            .brand-copy { margin: 36px 0 18px; }
            .brand-copy h2 { font-size: 34px; }
            .brand-note { display: none; }
            .form-panel { padding: 30px 24px 34px; }
            h1 { font-size: 34px; }
        }
    </style>
    @include('partials.responsive-polish')
</head>
<body>
    <main class="auth-layout">
        <aside class="brand-panel" aria-label="Honey's Bite">
            <p class="brand-name"><span class="brand-mark" aria-hidden="true"></span>Honey's Bite</p>
            <div class="brand-copy">
                <p>Hangat dari dapur kami</p>
                <h2>Tempat rasa nyaman dimulai.</h2>
            </div>
            <p class="brand-note">Roti dibuat dengan perhatian, untuk hari-hari yang lebih manis.</p>
        </aside>

        <section class="form-panel" aria-labelledby="form-title">
            <div class="form-content">
                <p class="eyebrow">Bergabung dengan kami</p>
                <h1 id="form-title">Buat akun baru</h1>
                <p class="intro">Daftarkan akun untuk menikmati pengalaman Honey's Bite.</p>

                <form method="POST" action="{{ url('/register') }}">
                    @csrf

                    <div class="field">
                        <label for="name">Nama lengkap</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" maxlength="255" required autofocus aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" @if ($errors->has('name')) aria-describedby="name-error" @endif>
                        @error('name')
                            <p class="field-error" id="name-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="email">Alamat email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" required aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" @if ($errors->has('email')) aria-describedby="email-error" @endif>
                        @error('email')
                            <p class="field-error" id="email-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password">Kata sandi</label>
                        <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}" @if ($errors->has('password')) aria-describedby="password-error" @endif>
                        @error('password')
                            <p class="field-error" id="password-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password_confirmation">Konfirmasi kata sandi</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required aria-invalid="{{ $errors->has('password_confirmation') ? 'true' : 'false' }}" @if ($errors->has('password_confirmation')) aria-describedby="password-confirmation-error" @endif>
                        @error('password_confirmation')
                            <p class="field-error" id="password-confirmation-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <button class="submit-button" type="submit">Daftar</button>
                </form>

                <p class="switch-copy">Sudah memiliki akun? <a href="{{ route('login') }}">Masuk</a></p>
            </div>
        </section>
    </main>
</body>
</html>