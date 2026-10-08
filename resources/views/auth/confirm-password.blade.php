<x-guest-layout>
    <h1 class="text-[26px] font-extrabold tracking-tight text-ink-900 dark:text-white">Confirm your password</h1>
    <p class="mt-1.5 text-sm text-ink-500">This is a secure area. Please confirm your password to continue.</p>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-7 space-y-5">
        @csrf
        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" autofocus />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>
        <button type="submit" class="btn btn-primary w-full py-2.5">{{ __('Confirm') }}</button>
    </form>
</x-guest-layout>
