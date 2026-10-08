<?php

namespace Tests\Feature\Domain;

use App\Models\Site;
use App\Models\User;
use App\Support\HealthCheck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    private function userAt(string $usertype, ?Site $site = null): User
    {
        $user = User::factory()->create([
            'usertype' => $usertype,
            'email' => fake()->unique()->userName() . '@multiculturalcls.org',
        ]);

        if ($site) {
            $user->sites()->attach($site->id);
        }

        return $user;
    }

    public function test_admin_can_open_the_health_screen(): void
    {
        $this->actingAs($this->userAt('admin'))
            ->get('/health')
            ->assertOk()
            ->assertSee('Application')
            ->assertSee('Database')
            ->assertSee('Server');
    }

    public function test_siteadmin_can_open_the_health_screen(): void
    {
        $this->actingAs($this->userAt('siteadmin'))->get('/health')->assertOk();
    }

    /** The checks report server internals, so everyone below siteadmin is bounced. */
    public function test_lower_roles_cannot_reach_the_health_screen(): void
    {
        $site = Site::factory()->create();

        foreach (['employee', 'supervisor', 'manager', 'director'] as $role) {
            $this->actingAs($this->userAt($role, $site))
                ->get('/health')
                ->assertRedirect('/dashboard');
        }
    }

    public function test_health_screen_is_closed_to_guests(): void
    {
        $this->get('/health')->assertRedirect('/login');
    }

    public function test_json_endpoint_returns_the_full_report(): void
    {
        $response = $this->actingAs($this->userAt('admin'))->get('/health.json');

        $response->assertJsonStructure([
            'status',
            'totals' => ['passing', 'warnings', 'failing', 'total'],
            'checked_at',
            'groups' => [['key', 'label', 'status', 'counts', 'checks']],
        ]);

        // A health endpoint should not answer 200 while something is failing.
        $expected = $response->json('status') === 'fail' ? 503 : 200;
        $response->assertStatus($expected);
    }

    public function test_report_covers_eight_groups_and_totals_add_up(): void
    {
        $report = (new HealthCheck)->run();

        $this->assertCount(8, $report['groups']);

        $counted = array_sum(array_map(fn ($g) => count($g['checks']), $report['groups']));
        $this->assertSame($counted, $report['totals']['total']);
        $this->assertSame(
            $report['totals']['total'],
            $report['totals']['passing'] + $report['totals']['warnings'] + $report['totals']['failing']
        );
    }

    public function test_every_group_status_reflects_its_worst_check(): void
    {
        foreach ((new HealthCheck)->run()['groups'] as $group) {
            $statuses = array_column($group['checks'], 'status');
            $expected = in_array('fail', $statuses, true) ? 'fail'
                : (in_array('warn', $statuses, true) ? 'warn' : 'pass');

            $this->assertSame($expected, $group['status'], "Group {$group['label']} rolled up wrongly");
        }
    }
}
