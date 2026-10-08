<?php

namespace Tests\Feature\Domain;

use App\Models\FormData;
use App\Models\Resident;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the six critical authorization holes. Each test fails against the code as it
 * stood before the fix.
 */
class AuthorizationTest extends TestCase
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

    /** SEC-5 */
    public function test_dashboard_is_not_reachable_by_guests(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_dashboard_is_reachable_once_logged_in(): void
    {
        $site = Site::factory()->create();

        $this->actingAs($this->userAt('employee', $site))
            ->get('/dashboard')
            ->assertOk();
    }

    /** SEC-1 */
    public function test_employee_cannot_impersonate_an_admin(): void
    {
        $site = Site::factory()->create();
        $admin = $this->userAt('admin');

        $this->actingAs($this->userAt('employee', $site))
            ->get('/users/login/' . $admin->id)
            ->assertForbidden();
    }

    public function test_admin_can_still_impersonate_a_site_user(): void
    {
        $site = Site::factory()->create();
        $employee = $this->userAt('employee', $site);

        $this->actingAs($this->userAt('admin'))
            ->get('/users/login/' . $employee->id)
            ->assertRedirect();
    }

    /** SEC-2 */
    public function test_user_cannot_delete_a_log_from_another_site(): void
    {
        $mySite = Site::factory()->create();
        $otherSite = Site::factory()->create();

        $log = FormData::factory()->create([
            'site_id' => $otherSite->id,
            'resident_id' => Resident::factory()->create(['site_id' => $otherSite->id])->id,
        ]);

        $this->actingAs($this->userAt('supervisor', $mySite))
            ->delete('/form/data/' . $log->id)
            ->assertNotFound();

        $this->assertDatabaseHas('form_data', ['id' => $log->id]);
    }

    public function test_admin_can_delete_any_log(): void
    {
        $site = Site::factory()->create();
        $log = FormData::factory()->create([
            'site_id' => $site->id,
            'resident_id' => Resident::factory()->create(['site_id' => $site->id])->id,
        ]);

        $this->actingAs($this->userAt('admin'))
            ->delete('/form/data/' . $log->id)
            ->assertOk();

        $this->assertDatabaseMissing('form_data', ['id' => $log->id]);
    }

    /** SEC-3 */
    public function test_supervisor_cannot_edit_a_log_belonging_to_another_site(): void
    {
        $mySite = Site::factory()->create();
        $otherSite = Site::factory()->create();

        $log = FormData::factory()->create([
            'site_id' => $otherSite->id,
            'resident_id' => Resident::factory()->create(['site_id' => $otherSite->id])->id,
            'medical' => 'original',
        ]);

        $this->actingAs($this->userAt('supervisor', $mySite))
            ->put('/log/data/' . $log->id, [
                'employee_type' => 'mcls',
                'log_date' => now()->toDateString(),
                'shift' => 'morning',
                'site_id' => $mySite->id,
                'medical' => 'tampered',
            ])
            ->assertSessionHasErrors('site_id');

        $this->assertSame('original', $log->fresh()->medical);
    }

    public function test_supervisor_can_edit_a_log_at_their_own_site(): void
    {
        $site = Site::factory()->create();
        $log = FormData::factory()->create([
            'site_id' => $site->id,
            'resident_id' => Resident::factory()->create(['site_id' => $site->id])->id,
            'medical' => 'original',
        ]);

        $this->actingAs($this->userAt('supervisor', $site))
            ->put('/log/data/' . $log->id, [
                'employee_type' => 'mcls',
                'log_date' => now()->toDateString(),
                'shift' => 'morning',
                'site_id' => $site->id,
                'medical' => 'updated',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('updated', $log->fresh()->medical);
    }

    /** SEC-4 */
    public function test_user_cannot_toggle_checklist_settings_for_another_site(): void
    {
        $mySite = Site::factory()->create();
        $otherSite = Site::factory()->create();
        $this->seed(\Database\Seeders\XwalkSeeder::class);
        $taskId = \DB::table('xwalk_site_checklist_type')->value('id');

        $this->actingAs($this->userAt('supervisor', $mySite))
            ->postJson('/admin/settings/toggle', [
                'site_id' => $otherSite->id,
                'task_id' => $taskId,
                'day' => 'mon',
                'enabled' => false,
            ])
            ->assertForbidden();

        // XwalkSeeder creates a settings row per site, so the row exists by design --
        // what matters is that the rejected toggle left it untouched.
        $this->assertDatabaseHas('site_checklist_settings', [
            'site_id' => $otherSite->id,
            'site_checklist_id' => $taskId,
            'mon_enabled_bool' => 1,
        ]);
    }

    public function test_supervisor_can_toggle_checklist_settings_for_their_own_site(): void
    {
        $site = Site::factory()->create();
        $this->seed(\Database\Seeders\XwalkSeeder::class);
        $taskId = \DB::table('xwalk_site_checklist_type')->value('id');

        $this->actingAs($this->userAt('supervisor', $site))
            ->postJson('/admin/settings/toggle', [
                'site_id' => $site->id,
                'task_id' => $taskId,
                'day' => 'mon',
                'enabled' => false,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('site_checklist_settings', [
            'site_id' => $site->id,
            'mon_enabled_bool' => 0,
        ]);
    }

    /** SEC-6 */
    public function test_residents_of_another_site_are_not_exposed(): void
    {
        $mySite = Site::factory()->create();
        $otherSite = Site::factory()->create();
        Resident::factory()->create(['site_id' => $otherSite->id, 'name' => 'Private Person']);

        $response = $this->actingAs($this->userAt('employee', $mySite))
            ->postJson('/get-residents', ['site_id' => $otherSite->id]);

        $response->assertOk();
        $this->assertSame([], $response->json());
    }

    public function test_residents_of_own_site_are_returned(): void
    {
        $site = Site::factory()->create();
        Resident::factory()->create(['site_id' => $site->id, 'name' => 'Alex Doe']);

        $this->actingAs($this->userAt('employee', $site))
            ->postJson('/get-residents', ['site_id' => $site->id])
            ->assertOk()
            ->assertJsonFragment(['name' => 'Alex Doe']);
    }
}
