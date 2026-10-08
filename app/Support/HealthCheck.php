<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;

/**
 * Runs the operational checks shown on the health screen.
 *
 * Every check returns one of three states -- pass, warn, fail -- plus the value that was
 * observed, so the page can show what it found rather than just a colour.
 */
class HealthCheck
{
    public const PASS = 'pass';
    public const WARN = 'warn';
    public const FAIL = 'fail';

    /** Documents forms accept uploads of this size, so PHP must allow at least it. */
    private const REQUIRED_UPLOAD_BYTES = 5 * 1024 * 1024;

    public function run(): array
    {
        $groups = [
            $this->application(),
            $this->database(),
            $this->storage(),
            $this->records(),
            $this->security(),
            $this->frontend(),
            $this->mail(),
            $this->server(),
        ];

        foreach ($groups as $i => $group) {
            $statuses = array_column($group['checks'], 'status');
            $groups[$i]['status'] = in_array(self::FAIL, $statuses, true) ? self::FAIL
                : (in_array(self::WARN, $statuses, true) ? self::WARN : self::PASS);
            $groups[$i]['counts'] = [
                self::PASS => count(array_keys($statuses, self::PASS, true)),
                self::WARN => count(array_keys($statuses, self::WARN, true)),
                self::FAIL => count(array_keys($statuses, self::FAIL, true)),
            ];
        }

        $all = array_merge(...array_map(fn ($g) => $g['checks'], $groups));
        $statuses = array_column($all, 'status');

        return [
            'groups' => $groups,
            'totals' => [
                'passing' => count(array_keys($statuses, self::PASS, true)),
                'warnings' => count(array_keys($statuses, self::WARN, true)),
                'failing' => count(array_keys($statuses, self::FAIL, true)),
                'total' => count($statuses),
            ],
            'status' => in_array(self::FAIL, $statuses, true) ? self::FAIL
                : (in_array(self::WARN, $statuses, true) ? self::WARN : self::PASS),
            'checked_at' => Carbon::now(),
        ];
    }

    // ---------------------------------------------------------------- groups

    private function application(): array
    {
        $debug = (bool) config('app.debug');
        $env = (string) config('app.env');

        return $this->group('application', 'Application', 'settings', 'brand', [
            $this->check('PHP version', PHP_VERSION, version_compare(PHP_VERSION, '8.1', '>=')),
            $this->check('Laravel version', app()->version(), true),
            $this->check('Environment', $env, true),
            $this->check(
                'Debug mode',
                $debug ? 'Enabled' : 'Disabled',
                $debug && $env === 'production' ? self::FAIL : ($debug ? self::WARN : self::PASS),
                $debug ? 'Must be off in production' : null
            ),
            $this->check('Application key', config('app.key') ? 'Set' : 'Missing', (bool) config('app.key')),
            $this->check('App URL', (string) config('app.url'), (bool) config('app.url')),
            $this->check('Config cache', app()->configurationIsCached() ? 'Cached' : 'Not cached', true),
            $this->check('Route cache', app()->routesAreCached() ? 'Cached' : 'Not cached', true),
            $this->check('View cache', File::isDirectory(storage_path('framework/views')) && count(File::files(storage_path('framework/views'))) ? 'Warm' : 'Cold', true),
        ]);
    }

    private function database(): array
    {
        try {
            DB::connection()->getPdo();
            $version = DB::selectOne('select version() as v')->v ?? 'unknown';

            $ran = DB::table('migrations')->pluck('migration')->all();
            $files = collect(File::files(database_path('migrations')))
                ->map(fn ($f) => $f->getFilenameWithoutExtension())->all();
            $pending = count(array_diff($files, $ran));

            $counts = [];
            foreach (['users', 'sites', 'residents', 'form_data'] as $table) {
                $counts[$table] = DB::table($table)->count();
            }

            return $this->group('database', 'Database', 'database', 'sky', [
                $this->check('Connection', 'Connected (' . config('database.default') . ')', true),
                $this->check('Server version', explode('-', $version)[0], true),
                $this->check('Migrations', $pending ? $pending . ' pending' : 'Up to date', $pending === 0, $pending ? 'Run php artisan migrate' : null),
                $this->check('Migrations applied', count($ran) . ' recorded', count($ran) > 0),
                $this->check('User accounts', number_format($counts['users']), $counts['users'] > 0),
                $this->check('Sites configured', number_format($counts['sites']), $counts['sites'] > 0 ? self::PASS : self::WARN, $counts['sites'] ? null : 'No sites yet'),
                $this->check('Residents recorded', number_format($counts['residents']), $counts['residents'] > 0 ? self::PASS : self::WARN),
                $this->check('Resident logs', number_format($counts['form_data']), true),
            ]);
        } catch (Throwable $e) {
            return $this->group('database', 'Database', 'database', 'sky', [
                $this->check('Connection', 'Unreachable', self::FAIL, $e->getMessage()),
            ]);
        }
    }

    private function storage(): array
    {
        $paths = [
            'storage/app' => storage_path('app'),
            'storage/framework' => storage_path('framework'),
            'storage/framework/cache' => storage_path('framework/cache'),
            'storage/framework/sessions' => storage_path('framework/sessions'),
            'storage/framework/views' => storage_path('framework/views'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ];

        $checks = [];
        foreach ($paths as $label => $path) {
            $checks[] = $this->check(
                $label,
                is_dir($path) ? (is_writable($path) ? 'Writable' : 'Not writable') : 'Missing',
                is_dir($path) && is_writable($path)
            );
        }

        $log = storage_path('logs/laravel.log');
        $size = is_file($log) ? filesize($log) : 0;
        $checks[] = $this->check(
            'Log file size',
            $this->bytes($size),
            $size < 50 * 1024 * 1024 ? self::PASS : self::WARN,
            $size >= 50 * 1024 * 1024 ? 'Consider rotating the log' : null
        );

        return $this->group('storage', 'Storage', 'storage', 'violet', $checks);
    }

    private function records(): array
    {
        try {
            $logs = DB::table('form_data')->count();
            $recent = DB::table('form_data')->where('log_date', '>=', Carbon::now()->subDays(7)->toDateString())->count();
            $weekStart = Carbon::now()->startOfWeek(Carbon::SUNDAY)->toDateString();
            $checklist = DB::table('site_checklist_data')->whereDate('week_start', $weekStart)->count();
            $catalogue = DB::table('xwalk_site_checklist_type')->where('is_deleted', 0)->count();

            return $this->group('records', 'Records', 'file-text', 'amber', [
                $this->check('Resident logs', number_format($logs), true),
                $this->check('Logged in last 7 days', number_format($recent), $recent > 0 ? self::PASS : self::WARN, $recent ? null : 'No shift logs recorded this week'),
                $this->check('Checklist entries this week', number_format($checklist), true),
                $this->check('Checklist task catalogue', number_format($catalogue) . ' tasks', $catalogue > 0 ? self::PASS : self::FAIL, $catalogue ? null : 'Run the XwalkSeeder'),
            ]);
        } catch (Throwable $e) {
            return $this->group('records', 'Records', 'file-text', 'amber', [
                $this->check('Records', 'Unavailable', self::FAIL, $e->getMessage()),
            ]);
        }
    }

    private function security(): array
    {
        $url = (string) config('app.url');
        $isProd = config('app.env') === 'production';

        try {
            $roleless = DB::table('users')->whereNull('usertype')->count();
        } catch (Throwable $e) {
            $roleless = 0;
        }

        return $this->group('security', 'Security', 'shield', 'rose', [
            $this->check('Debug disabled', config('app.debug') ? 'No' : 'Yes', config('app.debug') ? ($isProd ? self::FAIL : self::WARN) : self::PASS),
            $this->check('HTTPS app URL', str_starts_with($url, 'https://') ? 'Yes' : 'No', str_starts_with($url, 'https://') ? self::PASS : ($isProd ? self::FAIL : self::WARN), $isProd ? null : 'Local development'),
            $this->check('Session encryption', config('session.encrypt') ? 'On' : 'Off', config('session.encrypt') ? self::PASS : self::WARN),
            $this->check('Accounts without a role', (string) $roleless, $roleless === 0, $roleless ? 'These users cannot be authorised' : null),
        ]);
    }

    private function frontend(): array
    {
        $manifest = public_path('build/manifest.json');
        $hasManifest = is_file($manifest);
        $entries = $hasManifest ? (json_decode(File::get($manifest), true) ?: []) : [];

        $built = function (string $entry) use ($entries) {
            $file = $entries[$entry]['file'] ?? null;

            return $file && is_file(public_path('build/' . $file));
        };

        $newest = 0;
        foreach (File::allFiles(resource_path()) as $file) {
            $newest = max($newest, $file->getMTime());
        }
        $manifestTime = $hasManifest ? filemtime($manifest) : 0;
        $stale = $hasManifest && $newest > $manifestTime;

        return $this->group('frontend', 'Front-end', 'browser', 'emerald', [
            $this->check('Vite manifest', $hasManifest ? 'Present' : 'Missing', $hasManifest, $hasManifest ? null : 'Run npm run build'),
            $this->check('App stylesheet', $built('resources/css/app.css') ? 'Built' : 'Missing', $built('resources/css/app.css')),
            $this->check('App script', $built('resources/js/app.js') ? 'Built' : 'Missing', $built('resources/js/app.js')),
            $this->check('Bundle freshness', $stale ? 'Stale' : 'Current', $stale ? self::WARN : self::PASS, $stale ? 'Sources changed since the last build' : null),
            $this->check('Logo asset', is_file(public_path('logo.png')) ? 'Present' : 'Missing', is_file(public_path('logo.png'))),
            $this->check('Favicon', filesize(public_path('favicon.ico')) > 0 ? 'Present' : 'Empty file', filesize(public_path('favicon.ico')) > 0 ? self::PASS : self::WARN, 'Pages fall back to the logo'),
        ]);
    }

    private function mail(): array
    {
        $mailer = (string) config('mail.default');
        $host = (string) config('mail.mailers.smtp.host');
        $from = (string) config('mail.from.address');
        $smtp = $mailer === 'smtp';

        return $this->group('mail', 'Mail', 'mail', 'orange', [
            $this->check('Mailer', $mailer ?: 'Not set', (bool) $mailer),
            $this->check(
                'SMTP host',
                $host ?: 'Not configured',
                !$smtp ? self::PASS : ($host && $host !== 'mailpit' ? self::PASS : self::WARN),
                $smtp && (!$host || $host === 'mailpit') ? 'Password reset emails will not send' : null
            ),
            $this->check(
                'From address',
                $from ?: 'Not set',
                $from && $from !== 'hello@example.com' ? self::PASS : self::WARN,
                $from === 'hello@example.com' ? 'Still the framework default' : null
            ),
        ]);
    }

    private function server(): array
    {
        $upload = $this->toBytes((string) ini_get('upload_max_filesize'));
        $post = $this->toBytes((string) ini_get('post_max_size'));
        $memory = (string) ini_get('memory_limit');
        $free = @disk_free_space(base_path());
        $total = @disk_total_space(base_path());
        $pct = ($free && $total) ? ($free / $total) * 100 : null;

        return $this->group('server', 'Server', 'server', 'slate', [
            $this->check('Web server', ($_SERVER['SERVER_SOFTWARE'] ?? php_sapi_name()), true),
            $this->check(
                'Upload max filesize',
                ini_get('upload_max_filesize'),
                $upload >= self::REQUIRED_UPLOAD_BYTES ? self::PASS : self::FAIL,
                $upload >= self::REQUIRED_UPLOAD_BYTES ? null : 'Below the ' . $this->bytes(self::REQUIRED_UPLOAD_BYTES) . ' record uploads need'
            ),
            $this->check('Post max size', ini_get('post_max_size'), $post >= $upload),
            $this->check('Memory limit', $memory === '-1' ? 'Unlimited' : $memory, true),
            $this->check('Max execution time', ini_get('max_execution_time') . 's', true),
            $this->check(
                'Disk space',
                $free && $total ? $this->bytes($free) . ' free of ' . $this->bytes($total) . ' (' . round($pct) . '%)' : 'Unknown',
                $pct === null ? self::WARN : ($pct > 10 ? self::PASS : ($pct > 5 ? self::WARN : self::FAIL))
            ),
            $this->check('Server time', Carbon::now()->format('d M Y, H:i') . ' (' . config('app.timezone') . ')', true),
        ]);
    }

    // ---------------------------------------------------------------- helpers

    private function group(string $key, string $label, string $icon, string $tone, array $checks): array
    {
        return compact('key', 'label', 'icon', 'tone', 'checks');
    }

    /** $status accepts a bool for the common pass/fail case, or an explicit constant. */
    private function check(string $label, ?string $value, bool|string $status, ?string $hint = null): array
    {
        return [
            'label' => $label,
            'value' => $value === '' || $value === null ? '—' : $value,
            'status' => is_bool($status) ? ($status ? self::PASS : self::FAIL) : $status,
            'hint' => $hint,
        ];
    }

    private function toBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }

        $unit = strtolower($value[strlen($value) - 1]);
        $number = (int) $value;

        return match ($unit) {
            'g' => $number * 1024 ** 3,
            'm' => $number * 1024 ** 2,
            'k' => $number * 1024,
            default => $number,
        };
    }

    private function bytes(float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, $i > 2 ? 1 : 0) . ' ' . $units[$i];
    }
}
