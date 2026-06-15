@php
    $googleAuthEnabled = filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    $intent = $intent ?? 'login';
@endphp

@if($googleAuthEnabled)
    <div class="auth-google-divider">
        <span>veya</span>
    </div>

    <a
        href="{{ route('member.google.redirect', ['from' => $intent]) }}"
        class="auth-google-btn"
    >
        <svg class="auth-google-btn__icon" viewBox="0 0 24 24" aria-hidden="true">
            <path fill="#EA4335" d="M12 10.2v3.6h5.1c-.2 1.2-1.6 3.6-5.1 3.6-3.1 0-5.6-2.5-5.6-5.6S8.9 6.2 12 6.2c1.8 0 3 .8 3.7 1.4l2.5-2.4C16.5 3.9 14.4 3 12 3 7.6 3 4 6.6 4 11s3.6 8 8 8c4.6 0 7.7-3.2 7.7-7.8 0-.5 0-.9-.1-1.2H12z"/>
            <path fill="#34A853" d="M4.7 14.5 7.8 16.8c.9-2.6 3.4-4.5 6.2-4.5.9 0 1.7.2 2.5.5v-2.5C15.8 10 14.9 9.8 14 9.8c-2.8 0-5.3 1.9-6.2 4.5L4.7 14.5z"/>
            <path fill="#4A90E2" d="M12 19c2.4 0 4.4-.8 5.9-2.1l-2.8-2.3c-.8.5-1.8.9-3.1.9-2.4 0-4.4-1.6-5.1-3.8L4.7 14.5C6.2 17.4 8.9 19 12 19z"/>
            <path fill="#FBBC05" d="M19.9 12.3c0-.8-.1-1.6-.3-2.3H12v4.4h4.4c-.2 1-1 2.5-2.9 3.5l2.8 2.2c1.6-1.5 2.7-3.7 2.7-6.8z"/>
        </svg>
        <span>Google ile {{ $intent === 'register' ? 'üye ol' : 'giriş yap' }}</span>
    </a>

    <p class="auth-google-hint">Her Google hesabı yalnızca bir kez üye olabilir.</p>
@endif
