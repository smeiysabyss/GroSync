<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GROSYNC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/auth/login.css') }}" rel="stylesheet">
</head>
<body>

    {{-- Teks kiri --}}
    <div class="left-content">
        <h1>Permudah pencatatan stok dan penjualan toko grosirmu</h1>
    </div>

    {{-- Login Card --}}
    <div class="login-card">

        <p class="card-title">GROSYNC</p>

        {{-- Alert error — tampil di atas, satu pesan saja --}}
        @if($errors->any())
            <div class="alert-error">
                <span class="alert-icon">⚠</span>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Email --}}
            <div class="form-group">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Masukkan email"
                    value="{{ old('email') }}"
                    class="{{ $errors->has('email') ? 'input-error' : '' }}"
                    autocomplete="email"
                    required
                >
            </div>

            {{-- Password --}}
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan password"
                        class="{{ $errors->has('password') ? 'input-error' : '' }}"
                        autocomplete="current-password"
                        required
                    >
                    <button type="button" class="toggle-password" id="togglePasswordBtn" title="Lihat/sembunyikan password">
                        <svg id="iconEye" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg id="iconEyeOff" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 012.05-3.292M9.88 9.88A3 3 0 0114.12 14.12M6.1 6.1l11.8 11.8"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.94 17.94A10.05 10.05 0 0121.542 12c-1.274-4.057-5.065-7-9.542-7a9.97 9.97 0 00-4.394 1.006"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login">LOGIN</button>

        </form>
    </div>

    <script src="{{ asset('js/auth/login.js') }}"></script>

</body>
</html>