<x-app-layout>
    <x-page-header title="Profile" subtitle="Your account details and sign-in credentials." />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <x-card title="Profile information" subtitle="Update your name and email address.">
                <div class="max-w-xl">@include('profile.partials.update-profile-information-form')</div>
            </x-card>

            @if (Auth::user()->usertype != 'employee')
                <x-card title="Password" subtitle="Use a long, random password to stay secure.">
                    <div class="max-w-xl">@include('profile.partials.update-password-form')</div>
                </x-card>
            @endif
        </div>

        <div>
            <x-card title="Account">
                <dl class="space-y-4">
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-wide text-ink-500">Role</dt>
                        <dd class="mt-1.5"><x-role-badge :type="Auth::user()->usertype" /></dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-wide text-ink-500">Email</dt>
                        <dd class="mt-1 break-all text-sm text-ink-800 dark:text-ink-200">{{ Auth::user()->email }}</dd>
                    </div>
                    @if (Auth::user()->sites->isNotEmpty())
                        <div>
                            <dt class="text-[11px] font-semibold uppercase tracking-wide text-ink-500">Sites</dt>
                            <dd class="mt-1.5 flex flex-wrap gap-1">
                                @foreach (Auth::user()->sites as $s)
                                    <span class="pill pill-neutral">{{ $s->name }}</span>
                                @endforeach
                            </dd>
                        </div>
                    @endif
                    @if (Auth::user()->manager)
                        <div>
                            <dt class="text-[11px] font-semibold uppercase tracking-wide text-ink-500">Reports to</dt>
                            <dd class="mt-1 text-sm text-ink-800 dark:text-ink-200">{{ Auth::user()->manager->name }}</dd>
                        </div>
                    @endif
                </dl>
            </x-card>
        </div>
    </div>
</x-app-layout>
