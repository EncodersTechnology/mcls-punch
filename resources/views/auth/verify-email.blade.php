<x-guest-layout>
    <h1 class="text-[26px] font-extrabold tracking-tight text-ink-900 dark:text-white">Verify your email</h1>
    <p class="mt-1.5 text-sm text-ink-500">
        Thanks for signing up. Please confirm your email address by clicking the link we just sent you.
    </p>

    @if (session('status') == 'verification-link-sent')
        <x-alert class="mt-5">A new verification link has been sent to your email address.</x-alert>
    @endif

    <div class="mt-7 flex items-center gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary">{{ __('Resend email') }}</button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-ghost">{{ __('Log out') }}</button>
        </form>
    </div>
</x-guest-layout>
