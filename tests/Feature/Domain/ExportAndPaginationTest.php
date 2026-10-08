<?php

namespace Tests\Feature\Domain;

use App\Models\FormData;
use App\Models\Resident;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportAndPaginationTest extends TestCase
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

    private function logsAt(Site $site, int $count, array $attrs = []): void
    {
        $resident = Resident::factory()->create(['site_id' => $site->id]);
        FormData::factory()->count($count)->create(array_merge([
            'site_id' => $site->id,
            'resident_id' => $resident->id,
        ], $attrs));
    }

    private function download($response): string
    {
        ob_start();
        $response->baseResponse->sendContent();

        return ob_get_clean();
    }

    public function test_log_list_is_paginated(): void
    {
        $site = Site::factory()->create();
        $this->logsAt($site, 20);

        $this->actingAs($this->userAt('supervisor', $site))
            ->get('/employee/log/data')
            ->assertOk()
            ->assertSee('Showing', false)
            ->assertSee('of', false);

        // A second page exists and is reachable.
        $this->get('/employee/log/data?page=2')->assertOk();
    }

    public function test_log_export_streams_csv_scoped_to_accessible_sites(): void
    {
        $mySite = Site::factory()->create();
        $otherSite = Site::factory()->create();

        $this->logsAt($mySite, 2, ['mcls_name' => 'MINE-VISIBLE']);
        $this->logsAt($otherSite, 2, ['mcls_name' => 'HIDDEN-ELSEWHERE']);

        $response = $this->actingAs($this->userAt('supervisor', $mySite))->get('/logs/export');
        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $csv = $this->download($response);
        $this->assertStringContainsString('MINE-VISIBLE', $csv);
        $this->assertStringNotContainsString('HIDDEN-ELSEWHERE', $csv);
    }

    public function test_log_export_respects_the_active_filters(): void
    {
        $site = Site::factory()->create();
        $this->logsAt($site, 1, ['mcls_name' => 'IN-RANGE', 'log_date' => '2026-03-10']);
        $this->logsAt($site, 1, ['mcls_name' => 'OUT-OF-RANGE', 'log_date' => '2020-01-05']);

        $csv = $this->download(
            $this->actingAs($this->userAt('supervisor', $site))
                ->get('/logs/export?from_date=2026-03-01&to_date=2026-03-31')
        );

        $this->assertStringContainsString('IN-RANGE', $csv);
        $this->assertStringNotContainsString('OUT-OF-RANGE', $csv);
    }

    public function test_users_export_is_closed_to_site_users(): void
    {
        $site = Site::factory()->create();

        $this->actingAs($this->userAt('employee', $site))
            ->get('/admin/users/export')
            ->assertForbidden();
    }

    public function test_users_export_returns_csv_for_a_supervisor(): void
    {
        $site = Site::factory()->create();
        $this->userAt('employee', $site);

        $response = $this->actingAs($this->userAt('supervisor', $site))->get('/admin/users/export');
        $response->assertOk();

        $this->assertStringContainsString('Name,Email,Role', $this->download($response));
    }

    public function test_sites_and_residents_screen_paginates_and_exports(): void
    {
        Site::factory()->count(14)->create();
        $admin = $this->userAt('admin');

        $this->actingAs($admin)->get('/admin/site-resident')->assertOk()->assertSee('Showing', false);
        $this->actingAs($admin)->get('/admin/site-resident?sites_page=2')->assertOk();

        $response = $this->actingAs($admin)->get('/admin/site-resident/export');
        $response->assertOk();
        $this->assertStringContainsString('Site,"Shift 1"', $this->download($response));
    }

    public function test_user_list_search_filter_narrows_results(): void
    {
        $site = Site::factory()->create();
        User::factory()->create([
            'usertype' => 'employee', 'name' => 'Findable Person',
            'email' => 'findable@multiculturalcls.org',
        ])->sites()->attach($site->id);
        User::factory()->create([
            'usertype' => 'employee', 'name' => 'Someone Else',
            'email' => 'other@multiculturalcls.org',
        ])->sites()->attach($site->id);

        $this->actingAs($this->userAt('supervisor', $site))
            ->get('/acess/management?search=Findable')
            ->assertOk()
            ->assertSee('Findable Person')
            ->assertDontSee('Someone Else');
    }
}
