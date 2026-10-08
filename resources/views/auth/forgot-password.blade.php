<x-guest-layout>
    <h1 class="text-[26px] font-extrabold tracking-tight text-ink-900 dark:text-white">Reset your password</h1>
    <p class="mt-1.5 text-sm text-ink-500">
        Enter your email and we'll send you a reset link.
    </p>

    <div class="mt-5 flex items-start gap-2.5 rounded-lg border border-amber-200 bg-amber-50 px-3.5 py-3 text-xs text-amber-900 dark:border-amber-900 dark:bg-amber-950/50 dark:text-amber-200">
        <x-icon name="alert" class="mt-px h-4 w-4 shrink-0" />
        <span>Self-service reset is available to supervisor accounts. Everyone else should ask an administrator.</span>
    </div>

    <x-auth-session-status class="mt-5" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5">
        @csrf
        <div>
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus
                          placeholder="you@multiculturalcls.org" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>
        <button type="submit" class="btn btn-primary w-full py-2.5">{{ __('Email password reset link') }}</button>
    </form>

    <a href="{{ route('login') }}" class="mt-6 flex items-center justify-center gap-1.5 text-sm font-semibold text-ink-500 transition hover:text-ink-800 dark:hover:text-ink-200">
        <x-icon name="chevron-l" class="h-4 w-4" /> Back to sign in
    </a>
</x-guest-layout>
