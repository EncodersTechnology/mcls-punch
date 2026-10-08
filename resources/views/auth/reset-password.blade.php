<x-guest-layout>
    <h1 class="text-[26px] font-extrabold tracking-tight text-ink-900 dark:text-white">Choose a new password</h1>
    <p class="mt-1.5 text-sm text-ink-500">Must be at least 8 characters.</p>

    <form method="POST" action="{{ route('password.store') }}" class="mt-7 space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div>
            <x-input-label for="password" :value="__('New password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm password')" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        <button type="submit" class="btn btn-primary w-full py-2.5">{{ __('Reset password') }}</button>
    </form>
</x-guest-layout>
