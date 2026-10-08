<x-guest-layout>
    <h1 class="text-[26px] font-extrabold tracking-tight text-ink-900 dark:text-white">Welcome back</h1>
    <p class="mt-1.5 text-sm text-ink-500">Sign in to continue to the logging system.</p>

    <x-auth-session-status class="mt-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-7 space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus
                          autocomplete="username" placeholder="you@multiculturalcls.org" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div x-data="{ show: false }">
            <x-input-label for="password" :value="__('Password')" />
            <div class="relative">
                <x-text-input id="password" name="password" required autocomplete="current-password"
                              class="pr-11" x-bind:type="show ? 'text' : 'password'" type="password" />
                <button type="button" @click="show = !show" tabindex="-1"
                        class="absolute inset-y-0 right-0 grid w-11 place-items-center text-ink-400 transition hover:text-ink-600 dark:hover:text-ink-200"
                        :aria-label="show ? 'Hide password' : 'Show password'">
                    <x-icon name="eye" class="h-[18px] w-[18px]" />
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex cursor-pointer items-center gap-2">
                <input id="remember_me" type="checkbox" name="remember"
                       class="h-4 w-4 rounded border-ink-300 text-brand-600 focus:ring-brand-500 dark:border-ink-700 dark:bg-ink-900">
                <span class="text-sm text-ink-600 dark:text-ink-400">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-semibold text-brand-700 transition hover:text-brand-600 dark:text-brand-400"
                   href="{{ route('password.request') }}">{{ __('Forgot password?') }}</a>
            @endif
        </div>

        <button type="submit" class="btn btn-primary w-full py-2.5">{{ __('Sign in') }}</button>
    </form>

    <p class="mt-8 text-center text-xs text-ink-400">
        Accounts are created by your administrator. Contact them if you need access.
    </p>
</x-guest-layout>
