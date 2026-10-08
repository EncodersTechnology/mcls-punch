@php
    $managerOptions = $allUsers->whereIn('usertype', ['director', 'manager'])->values();
    $hasFilters = collect(['search', 'usertype', 'site_id'])->contains(fn ($k) => request()->filled($k));
@endphp

<x-app-layout>
    <div x-data="{
            modal: null,
            form: { id: null, name: '', email: '', usertype: '', manager_id: '', site_ids: [], site_id: '' },
            get needsManager() { return ['supervisor', 'manager', 'director'].includes(this.form.usertype) },
            get needsSite()    { return ['supervisor', 'employee'].includes(this.form.usertype) },
            get multiSite()    { return this.form.usertype === 'supervisor' },
            openCreate() {
                this.form = { id: null, name: '', email: '', usertype: '', manager_id: '', site_ids: [], site_id: '' };
                this.modal = 'create';
            },
            openEdit(u) {
                const ids = u.site_ids.map(String);
                this.form = { id: u.id, name: u.name, email: u.email, usertype: u.usertype,
                              manager_id: u.manager_id ?? '', site_ids: ids, site_id: ids[0] ?? '' };
                this.modal = 'edit';
            }
        }">

        <x-page-header title="Access Management"
                       subtitle="People who can record and review logs, and the sites they cover.">
            <x-slot name="actions">
                <a href="{{ route('user.export', request()->query()) }}" class="btn btn-ghost">
                    <x-icon name="download" class="h-4 w-4" /> Export CSV
                </a>
                @if (count($manageableUserTypes))
                    <button type="button" @click="openCreate()" class="btn btn-primary">
                        <x-icon name="plus" class="h-4 w-4" /> Add user
                    </button>
                @endif
            </x-slot>
        </x-page-header>

        {{-- Filters --}}
        <form method="GET" action="{{ route('site.access.index') }}" class="card card-pad mb-5">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="lg:col-span-2">
                    <label for="u-search" class="field-label">Search</label>
                    <div class="relative">
                        <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" />
                        <input id="u-search" type="search" name="search" value="{{ request('search') }}"
                               placeholder="Name or email…" class="w-full rounded-lg pl-9 text-sm">
                    </div>
                </div>
                <div>
                    <label for="u-role" class="field-label">Role</label>
                    <select id="u-role" name="usertype" class="w-full text-sm">
                        <option value="">All roles</option>
                        @foreach (['siteadmin' => 'Site Admin', 'director' => 'Director', 'manager' => 'Manager', 'supervisor' => 'Supervisor', 'employee' => 'Site User'] as $val => $lbl)
                            <option value="{{ $val }}" @selected(request('usertype') === $val)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="u-site" class="field-label">Site</label>
                    <select id="u-site" name="site_id" class="w-full text-sm">
                        <option value="">All sites</option>
                        @foreach ($sites as $s)
                            <option value="{{ $s->id }}" @selected(request('site_id') == $s->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-ink-200/80 pt-4 dark:border-ink-800">
                <button type="submit" class="btn btn-primary"><x-icon name="filter" class="h-4 w-4" /> Apply filters</button>
                @if ($hasFilters)<a href="{{ route('site.access.index') }}" class="btn btn-ghost">Clear</a>@endif
                <span class="ml-auto text-xs text-ink-500">
                    <span class="num font-semibold text-ink-700 dark:text-ink-300">{{ $users->total() }}</span> {{ Str::plural('user', $users->total()) }}
                </span>
            </div>
        </form>

        <div class="card overflow-hidden">
            @if ($users->count())
                <div class="tablewrap">
                    <table class="dtable">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Reports to</th>
                                <th>Sites</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                @php $userPayload = [
                                    'id' => $user->id, 'name' => $user->name, 'email' => $user->email,
                                    'usertype' => $user->usertype, 'manager_id' => $user->manager_id,
                                    'site_ids' => $user->sites->pluck('id'),
                                ]; @endphp
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-brand-100 text-xs font-bold text-brand-800 dark:bg-brand-950 dark:text-brand-200">
                                                {{ Str::of($user->name)->explode(' ')->take(2)->map(fn ($p) => Str::substr($p, 0, 1))->implode('') }}
                                            </span>
                                            <span class="min-w-0">
                                                <span class="block truncate font-semibold text-ink-900 dark:text-white">{{ $user->name }}</span>
                                                <span class="block truncate text-xs text-ink-500">{{ $user->email }}</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td><x-role-badge :type="$user->usertype" /></td>
                                    <td class="text-ink-600 dark:text-ink-400">{{ $user->manager->name ?? '—' }}</td>
                                    <td>
                                        @if ($user->sites->isNotEmpty())
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($user->sites->take(3) as $s)
                                                    <span class="pill pill-neutral">{{ $s->name }}</span>
                                                @endforeach
                                                @if ($user->sites->count() > 3)
                                                    <span class="pill pill-neutral">+{{ $user->sites->count() - 3 }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-ink-400">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button" class="btn btn-subtle btn-sm btn-icon" title="Edit"
                                                @click='openEdit(@json($userPayload))'>
                                                <x-icon name="edit" class="h-4 w-4" />
                                            </button>
                                            <a href="{{ route('users.login', $user->id) }}" title="Sign in as this user"
                                               class="btn btn-subtle btn-sm btn-icon">
                                                <x-icon name="impersonate" class="h-4 w-4" />
                                            </a>
                                            <form action="{{ route('user.destroy', $user->id) }}" method="POST"
                                                  onsubmit="return confirm('Delete this user? Their recorded logs are kept.');">
                                                @csrf @method('DELETE')
                                                <button type="submit" title="Delete"
                                                        class="btn btn-sm btn-icon text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/40">
                                                    <x-icon name="trash" class="h-4 w-4" />
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <x-pagination :paginator="$users" label="users" />
            @else
                <x-empty-state icon="users"
                    title="{{ $hasFilters ? 'No users match these filters' : 'No users to show' }}"
                    message="{{ $hasFilters ? 'Try a different search or role.' : 'Users you manage will appear here.' }}" />
            @endif
        </div>

        {{-- Create / edit modal --}}
        <div x-show="modal" x-cloak @keydown.escape.window="modal = null"
             class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-ink-950/60 p-4 backdrop-blur-sm sm:p-8">
            <div @click.outside="modal = null" x-transition
                 class="w-full max-w-xl rounded-xl2 border border-ink-200 bg-white shadow-lift dark:border-ink-800 dark:bg-ink-900">
                <form method="POST" :action="modal === 'edit' ? '/admin/user/update/' + form.id : '{{ route('user.store') }}'">
                    @csrf
                    <template x-if="modal === 'edit'"><input type="hidden" name="_method" value="PUT"></template>

                    <div class="card-head">
                        <h2 class="text-base font-bold text-ink-900 dark:text-white"
                            x-text="modal === 'edit' ? 'Edit user' : 'Add user'"></h2>
                        <button type="button" @click="modal = null" class="btn btn-subtle btn-icon"><x-icon name="close" /></button>
                    </div>

                    <div class="card-pad grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="field-label">Full name</label>
                            <input type="text" name="name" x-model="form.name" class="w-full text-sm" required>
                        </div>
                        <div>
                            <label class="field-label">Email</label>
                            <input type="email" name="email" x-model="form.email" class="w-full text-sm" required>
                            <p class="field-hint">Must be an @multiculturalcls.org address.</p>
                        </div>

                        <div>
                            <label class="field-label">Password</label>
                            <input type="password" name="password" class="w-full text-sm"
                                   :required="modal === 'create'" autocomplete="new-password">
                            <p class="field-hint" x-show="modal === 'edit'">Leave blank to keep the current password.</p>
                        </div>
                        <div>
                            <label class="field-label">Confirm password</label>
                            <input type="password" name="password_confirmation" class="w-full text-sm"
                                   :required="modal === 'create'" autocomplete="new-password">
                        </div>

                        <div>
                            <label class="field-label">Role</label>
                            <select name="usertype" x-model="form.usertype" class="w-full text-sm" required>
                                <option value="">Select role</option>
                                @foreach ($manageableUserTypes as $type)
                                    <option value="{{ $type }}">{{ $type === 'employee' ? 'Site User' : ucfirst($type) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="needsManager" x-cloak>
                            <label class="field-label">Reports to</label>
                            <select name="manager_id" x-model="form.manager_id" class="w-full text-sm">
                                <option value="">Select manager</option>
                                @foreach ($managerOptions as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }} ({{ ucfirst($m->usertype) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="needsSite" x-cloak class="sm:col-span-2">
                            <label class="field-label" x-text="multiSite ? 'Sites' : 'Site'"></label>

                            {{-- Supervisors post site_ids[]; site users post a single site_id. Two
                                 separate controls, because a single select cannot bind to an array. --}}
                            <div x-show="multiSite" x-cloak>
                                <select name="site_ids[]" multiple size="5" x-model="form.site_ids" class="w-full text-sm">
                                    @foreach ($sites as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                                <p class="field-hint">Hold Ctrl (or Cmd) to select several sites.</p>
                            </div>

                            <div x-show="!multiSite" x-cloak>
                                <select name="site_id" x-model="form.site_id" class="w-full text-sm">
                                    <option value="">Select site</option>
                                    @foreach ($sites as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 border-t border-ink-200/80 px-5 py-3.5 dark:border-ink-800">
                        <button type="button" @click="modal = null" class="btn btn-ghost">Cancel</button>
                        <button type="submit" class="btn btn-primary"><x-icon name="check" class="h-4 w-4" /> Save user</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
