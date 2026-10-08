@php $tab = request('tab', 'sites'); @endphp

<x-app-layout>
    <div x-data="{
            tab: '{{ $tab }}',
            siteModal: null, site: { id: null, name: '', shift_1: '', shift_2: '' },
            resModal: null,  res:  { id: null, name: '', site_id: '' },
            openSite(s)  { this.site = s ?? { id: null, name: '', shift_1: '', shift_2: '' }; this.siteModal = s ? 'edit' : 'create'; },
            openRes(r)   { this.res  = r ?? { id: null, name: '', site_id: '' };             this.resModal  = r ? 'edit' : 'create'; }
         }">

        <x-page-header title="Sites and Residents"
                       subtitle="Locations covered by the service and the people living at each.">
            <x-slot name="actions">
                <a href="{{ route('admin.resident.export') }}" class="btn btn-ghost">
                    <x-icon name="download" class="h-4 w-4" /> Export CSV
                </a>
                <button type="button" @click="tab === 'sites' ? openSite(null) : openRes(null)" class="btn btn-primary">
                    <x-icon name="plus" class="h-4 w-4" />
                    <span x-text="tab === 'sites' ? 'Add site' : 'Add resident'"></span>
                </button>
            </x-slot>
        </x-page-header>

        {{-- Tabs --}}
        <div class="mb-5 inline-flex rounded-lg border border-ink-200 bg-white p-1 dark:border-ink-800 dark:bg-ink-900">
            <button type="button" @click="tab = 'sites'"
                    :class="tab === 'sites' ? 'bg-brand-600 text-white shadow-sm' : 'text-ink-600 hover:bg-ink-100 dark:text-ink-400 dark:hover:bg-ink-800'"
                    class="flex items-center gap-2 rounded-md px-4 py-2 text-sm font-semibold transition">
                <x-icon name="sites" class="h-4 w-4" /> Sites
                <span class="pill pill-neutral num">{{ $sites->total() }}</span>
            </button>
            <button type="button" @click="tab = 'residents'"
                    :class="tab === 'residents' ? 'bg-brand-600 text-white shadow-sm' : 'text-ink-600 hover:bg-ink-100 dark:text-ink-400 dark:hover:bg-ink-800'"
                    class="flex items-center gap-2 rounded-md px-4 py-2 text-sm font-semibold transition">
                <x-icon name="users" class="h-4 w-4" /> Residents
                <span class="pill pill-neutral num">{{ $residents->total() }}</span>
            </button>
        </div>

        {{-- Sites --}}
        <div x-show="tab === 'sites'" x-cloak>
            <form method="GET" action="{{ route('admin.resident') }}" class="card card-pad mb-4">
                <input type="hidden" name="tab" value="sites">
                <div class="flex flex-wrap items-end gap-3">
                    <div class="min-w-[220px] flex-1">
                        <label for="s-search" class="field-label">Search sites</label>
                        <div class="relative">
                            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" />
                            <input id="s-search" type="search" name="site_search" value="{{ $siteSearch }}"
                                   placeholder="Site name…" class="w-full rounded-lg pl-9 text-sm">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><x-icon name="filter" class="h-4 w-4" /> Search</button>
                    @if ($siteSearch)<a href="{{ route('admin.resident') }}" class="btn btn-ghost">Clear</a>@endif
                </div>
            </form>

            <div class="card overflow-hidden">
                @if ($sites->count())
                    <div class="tablewrap">
                        <table class="dtable">
                            <thead>
                                <tr><th>Site</th><th>Shift 1</th><th>Shift 2</th><th>Residents</th><th class="text-right">Actions</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($sites as $site)
                                    @php $sitePayload = ['id' => $site->id, 'name' => $site->name, 'shift_1' => $site->shift_1, 'shift_2' => $site->shift_2]; @endphp
                                    <tr>
                                        <td class="font-semibold text-ink-900 dark:text-white">{{ $site->name }}</td>
                                        <td>{{ $site->shift_1 ?: '—' }}</td>
                                        <td>{{ $site->shift_2 ?: '—' }}</td>
                                        <td><span class="pill pill-neutral num">{{ $site->residents_count }}</span></td>
                                        <td>
                                            <div class="flex items-center justify-end gap-1">
                                                <button type="button" class="btn btn-subtle btn-sm btn-icon" title="Edit"
                                                        @click='openSite(@json($sitePayload))'>
                                                    <x-icon name="edit" class="h-4 w-4" />
                                                </button>
                                                <form action="{{ route('sites.destroy', $site) }}" method="POST"
                                                      onsubmit="return confirm('Delete this site? Sites with recorded logs cannot be deleted.');">
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
                    <x-pagination :paginator="$sites" label="sites" />
                @else
                    <x-empty-state icon="sites" title="No sites found"
                                   message="Add a site to start recording resident logs against it." />
                @endif
            </div>
        </div>

        {{-- Residents --}}
        <div x-show="tab === 'residents'" x-cloak>
            <form method="GET" action="{{ route('admin.resident') }}" class="card card-pad mb-4">
                <input type="hidden" name="tab" value="residents">
                <div class="flex flex-wrap items-end gap-3">
                    <div class="min-w-[220px] flex-1">
                        <label for="r-search" class="field-label">Search residents</label>
                        <div class="relative">
                            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" />
                            <input id="r-search" type="search" name="resident_search" value="{{ $residentSearch }}"
                                   placeholder="Resident name…" class="w-full rounded-lg pl-9 text-sm">
                        </div>
                    </div>
                    <div class="min-w-[180px]">
                        <label for="r-site" class="field-label">Site</label>
                        <select id="r-site" name="site_id" class="w-full text-sm">
                            <option value="">All sites</option>
                            @foreach ($allSites as $s)
                                <option value="{{ $s->id }}" @selected(request('site_id') == $s->id)>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary"><x-icon name="filter" class="h-4 w-4" /> Search</button>
                    @if ($residentSearch || request('site_id'))<a href="{{ route('admin.resident', ['tab' => 'residents']) }}" class="btn btn-ghost">Clear</a>@endif
                </div>
            </form>

            <div class="card overflow-hidden">
                @if ($residents->count())
                    <div class="tablewrap">
                        <table class="dtable">
                            <thead><tr><th>Resident</th><th>Site</th><th class="text-right">Actions</th></tr></thead>
                            <tbody>
                                @foreach ($residents as $resident)
                                    @php $resPayload = ['id' => $resident->id, 'name' => $resident->name, 'site_id' => $resident->site_id]; @endphp
                                    <tr>
                                        <td class="font-semibold text-ink-900 dark:text-white">{{ $resident->name }}</td>
                                        <td><span class="pill pill-neutral">{{ $resident->site->name ?? '—' }}</span></td>
                                        <td>
                                            <div class="flex items-center justify-end gap-1">
                                                <button type="button" class="btn btn-subtle btn-sm btn-icon" title="Edit"
                                                        @click='openRes(@json($resPayload))'>
                                                    <x-icon name="edit" class="h-4 w-4" />
                                                </button>
                                                <form action="{{ route('residents.destroy', $resident) }}" method="POST"
                                                      onsubmit="return confirm('Delete this resident? Residents with recorded logs cannot be deleted.');">
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
                    <x-pagination :paginator="$residents" label="residents" />
                @else
                    <x-empty-state icon="users" title="No residents found"
                                   message="Add residents so staff can record logs against them." />
                @endif
            </div>
        </div>

        {{-- Site modal --}}
        <div x-show="siteModal" x-cloak @keydown.escape.window="siteModal = null"
             class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-ink-950/60 p-4 backdrop-blur-sm sm:p-8">
            <div @click.outside="siteModal = null" x-transition
                 class="w-full max-w-lg rounded-xl2 border border-ink-200 bg-white shadow-lift dark:border-ink-800 dark:bg-ink-900">
                <form method="POST" :action="siteModal === 'edit' ? '/admin/sites/' + site.id : '{{ route('sites.store') }}'">
                    @csrf
                    <template x-if="siteModal === 'edit'"><input type="hidden" name="_method" value="PUT"></template>
                    <div class="card-head">
                        <h2 class="text-base font-bold text-ink-900 dark:text-white" x-text="siteModal === 'edit' ? 'Edit site' : 'Add site'"></h2>
                        <button type="button" @click="siteModal = null" class="btn btn-subtle btn-icon"><x-icon name="close" /></button>
                    </div>
                    <div class="card-pad grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="field-label">Site name</label>
                            <input type="text" name="name" x-model="site.name" class="w-full text-sm" required>
                        </div>
                        <div>
                            <label class="field-label">Shift 1 label</label>
                            <input type="text" name="shift_1" x-model="site.shift_1" class="w-full text-sm" placeholder="e.g. Morning" required>
                        </div>
                        <div>
                            <label class="field-label">Shift 2 label</label>
                            <input type="text" name="shift_2" x-model="site.shift_2" class="w-full text-sm" placeholder="e.g. Night" required>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-ink-200/80 px-5 py-3.5 dark:border-ink-800">
                        <button type="button" @click="siteModal = null" class="btn btn-ghost">Cancel</button>
                        <button type="submit" class="btn btn-primary"><x-icon name="check" class="h-4 w-4" /> Save site</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Resident modal --}}
        <div x-show="resModal" x-cloak @keydown.escape.window="resModal = null"
             class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-ink-950/60 p-4 backdrop-blur-sm sm:p-8">
            <div @click.outside="resModal = null" x-transition
                 class="w-full max-w-lg rounded-xl2 border border-ink-200 bg-white shadow-lift dark:border-ink-800 dark:bg-ink-900">
                <form method="POST" :action="resModal === 'edit' ? '/admin/residents/' + res.id : '{{ route('residents.store') }}'">
                    @csrf
                    <template x-if="resModal === 'edit'"><input type="hidden" name="_method" value="PUT"></template>
                    <div class="card-head">
                        <h2 class="text-base font-bold text-ink-900 dark:text-white" x-text="resModal === 'edit' ? 'Edit resident' : 'Add resident'"></h2>
                        <button type="button" @click="resModal = null" class="btn btn-subtle btn-icon"><x-icon name="close" /></button>
                    </div>
                    <div class="card-pad space-y-4">
                        <div>
                            <label class="field-label">Resident name</label>
                            <input type="text" name="name" x-model="res.name" class="w-full text-sm" required>
                        </div>
                        <div>
                            <label class="field-label">Site</label>
                            <select name="site_id" x-model="res.site_id" class="w-full text-sm" required>
                                <option value="">Select site</option>
                                @foreach ($allSites as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-ink-200/80 px-5 py-3.5 dark:border-ink-800">
                        <button type="button" @click="resModal = null" class="btn btn-ghost">Cancel</button>
                        <button type="submit" class="btn btn-primary"><x-icon name="check" class="h-4 w-4" /> Save resident</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
