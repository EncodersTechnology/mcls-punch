@php
    $totals = $report['totals'];
    $status = $report['status'];
    $total  = max(1, $totals['total']);

    $banner = match ($status) {
        'fail' => ['from-rose-700 via-rose-700 to-rose-900',  'x-circle', 'Attention required', 'One or more checks failed. Open the highlighted cards below.'],
        'warn' => ['from-amber-600 via-amber-600 to-amber-800', 'alert',    'Needs a look',      'Everything is running, but some checks want attention.'],
        default => ['from-brand-700 via-brand-700 to-brand-900', 'check',   'All systems normal', 'Every check passed on this environment.'],
    };
    [$bannerGradient, $bannerIcon, $bannerTitle, $bannerText] = $banner;

    // Literal class strings so Tailwind keeps them.
    $tones = [
        'brand'   => 'bg-brand-600',   'sky'     => 'bg-sky-600',
        'violet'  => 'bg-violet-600',  'amber'   => 'bg-amber-500',
        'rose'    => 'bg-rose-600',    'emerald' => 'bg-emerald-600',
        'orange'  => 'bg-orange-500',  'slate'   => 'bg-slate-600',
    ];

    $openState = collect($report['groups'])
        ->mapWithKeys(fn ($g) => [$g['key'] => $g['status'] !== 'pass'])
        ->toJson();
@endphp

<x-app-layout>
    <div x-data="{
            open: {{ $openState }},
            expandAll()   { for (const k in this.open) this.open[k] = true; },
            collapseAll() { for (const k in this.open) this.open[k] = false; }
         }">

        {{-- Status banner --}}
        <div class="relative overflow-hidden rounded-xl2 bg-gradient-to-r {{ $bannerGradient }} p-6 text-white shadow-lift sm:p-7">
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 opacity-[0.14]"
                 style="background-image:radial-gradient(circle at 1px 1px,#fff 1px,transparent 0);background-size:22px 22px"></div>
            <div aria-hidden="true" class="pointer-events-none absolute -right-16 -top-24 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>

            <div class="relative flex flex-wrap items-start justify-between gap-6">
                <div class="flex min-w-0 items-start gap-4">
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-white/20 ring-1 ring-white/25">
                        <x-icon :name="$bannerIcon" class="h-6 w-6" />
                    </span>
                    <div class="min-w-0">
                        <h1 class="text-[22px] font-extrabold tracking-tight sm:text-2xl">{{ $bannerTitle }}</h1>
                        <p class="mt-1 max-w-lg text-sm text-white/80">{{ $bannerText }}</p>
                    </div>
                </div>

                <div class="flex gap-2.5">
                    @foreach ([['passing', 'Passing'], ['warnings', 'Warnings'], ['failing', 'Failing']] as [$key, $label])
                        <div class="min-w-[86px] rounded-xl bg-white/12 px-4 py-3 text-center ring-1 ring-white/20 backdrop-blur-sm">
                            <p class="num text-2xl font-extrabold leading-none">{{ $totals[$key] }}</p>
                            <p class="mt-1.5 text-[10px] font-bold uppercase tracking-wider text-white/70">{{ $label }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Proportional breakdown --}}
            <div class="relative mt-6 flex h-2 gap-1 overflow-hidden rounded-full bg-white/15">
                @foreach ([['passing', 'bg-emerald-400'], ['warnings', 'bg-amber-300'], ['failing', 'bg-rose-400']] as [$key, $cls])
                    @if ($totals[$key])
                        <div class="{{ $cls }} rounded-full transition-all" style="width: {{ round(($totals[$key] / $total) * 100, 2) }}%"></div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Toolbar --}}
        <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
            <div class="flex gap-2">
                <button type="button" @click="expandAll()" class="btn btn-ghost btn-sm">
                    <x-icon name="chevron-d" class="h-4 w-4" /> Expand all
                </button>
                <button type="button" @click="collapseAll()" class="btn btn-ghost btn-sm">
                    <x-icon name="chevron-d" class="h-4 w-4 rotate-180" /> Collapse all
                </button>
            </div>
            <p class="text-xs text-ink-500">Cards with warnings or failures open automatically.</p>
        </div>

        {{-- Cards: four per row, two rows --}}
        <div class="mt-4 grid grid-cols-1 items-start gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($report['groups'] as $group)
                @php
                    $gs = $group['status'];
                    $ring = match ($gs) {
                        'fail' => 'ring-1 ring-rose-300 dark:ring-rose-900',
                        'warn' => 'ring-1 ring-amber-300 dark:ring-amber-900',
                        default => '',
                    };
                    $pill = match ($gs) {
                        'fail' => ['pill-danger', $group['counts']['fail'] . ' failing'],
                        'warn' => ['pill-warn', $group['counts']['warn'] . ' ' . Str::plural('warning', $group['counts']['warn'])],
                        default => ['pill-brand', 'All clear'],
                    };
                @endphp

                <section class="card overflow-hidden transition hover:shadow-lift {{ $ring }}">
                    <button type="button" @click="open['{{ $group['key'] }}'] = !open['{{ $group['key'] }}']"
                            class="flex w-full items-center gap-3 px-4 py-3.5 text-left transition hover:bg-ink-50 dark:hover:bg-ink-800/60"
                            :aria-expanded="open['{{ $group['key'] }}'] ? 'true' : 'false'">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl {{ $tones[$group['tone']] ?? 'bg-ink-500' }} text-white shadow-sm">
                            <x-icon :name="$group['icon']" class="h-5 w-5" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-bold text-ink-900 dark:text-white">{{ $group['label'] }}</span>
                            <span class="num block text-[11px] text-ink-500">{{ count($group['checks']) }} checks</span>
                        </span>
                        <span class="pill {{ $pill[0] }} shrink-0 whitespace-nowrap uppercase">{{ $pill[1] }}</span>
                        <x-icon name="chevron-d" class="h-4 w-4 shrink-0 text-ink-400 transition-transform"
                                x-bind:class="open['{{ $group['key'] }}'] && 'rotate-180'" />
                    </button>

                    <div x-show="open['{{ $group['key'] }}']" x-collapse.duration.200ms x-cloak
                         class="border-t border-ink-200/80 dark:border-ink-800">
                        <ul class="divide-y divide-ink-200/70 dark:divide-ink-800">
                            @foreach ($group['checks'] as $check)
                                @php
                                    $row = match ($check['status']) {
                                        'fail' => ['bg-rose-50 dark:bg-rose-950/30', 'text-rose-600 dark:text-rose-400', 'x-circle'],
                                        'warn' => ['bg-amber-50 dark:bg-amber-950/25', 'text-amber-600 dark:text-amber-400', 'alert'],
                                        default => ['', 'text-emerald-600 dark:text-emerald-400', 'check'],
                                    };
                                @endphp
                                <li class="flex items-start gap-2.5 px-4 py-2.5 {{ $row[0] }}">
                                    <x-icon :name="$row[2]" class="mt-0.5 h-4 w-4 shrink-0 {{ $row[1] }}" />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[13px] font-semibold leading-tight text-ink-900 dark:text-ink-100">{{ $check['label'] }}</p>
                                        <p class="num mt-0.5 break-words text-[12px] leading-snug text-ink-500">{{ $check['value'] }}</p>
                                        @if ($check['hint'])
                                            <p class="mt-1 text-[11px] font-medium leading-snug {{ $row[1] }}">{{ $check['hint'] }}</p>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </section>
            @endforeach
        </div>

        {{-- Footer --}}
        <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-ink-200/80 pt-5 dark:border-ink-800">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('health.index') }}" class="btn btn-primary">
                    <x-icon name="refresh" class="h-4 w-4" /> Re-run checks
                </a>
                <a href="{{ route('health.json') }}" target="_blank" rel="noopener" class="btn btn-ghost">
                    <x-icon name="code" class="h-4 w-4" /> JSON endpoint
                </a>
            </div>
            <p class="num text-xs text-ink-500">
                Last checked {{ $report['checked_at']->format('d M Y') }} at {{ $report['checked_at']->format('H:i') }}
            </p>
        </div>
    </div>
</x-app-layout>
