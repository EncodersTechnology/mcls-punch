<?php

namespace Tests\Feature\Domain;

use App\Models\FormData;
use App\Models\Resident;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The five findings the first audit missed (NEW-1 .. NEW-5).
 */
class SecondPassTest extends TestCase
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

    /** NEW-1 */
    public function test_dashboard_does_not_leak_the_latest_log_from_another_site(): void
    {
        $mySite = Site::factory()->create();
        $otherSite = Site::factory()->create();

        FormData::factory()->create([
            'site_id' => $otherSite->id,
            'resident_id' => Resident::factory()->create(['site_id' => $otherSite->id])->id,
            'medical' => 'CONFIDENTIAL-OTHER-SITE',
        ]);

        $this->actingAs($this->userAt('employee', $mySite))
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('CONFIDENTIAL-OTHER-SITE');
    }

    public function test_dashboard_still_shows_the_latest_log_from_your_own_site(): void
    {
        $site = Site::factory()->create();
        FormData::factory()->create([
            'site_id' => $site->id,
            'resident_id' => Resident::factory()->create(['site_id' => $site->id])->id,
            'medical' => 'MY-SITE-LOG',
        ]);

        $this->actingAs($this->userAt('employee', $site))
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('MY-SITE-LOG');
    }

    /** NEW-2 */
    public function test_form_data_query_does_not_expose_another_sites_log(): void
    {
        $mySite = Site::factory()->create();
        $otherSite = Site::factory()->create();

        FormData::factory()->create([
            'site_id' => $otherSite->id,
            'resident_id' => Resident::factory()->create(['site_id' => $otherSite->id])->id,
            'medical' => 'CONFIDENTIAL-OTHER-SITE',
        ]);

        $response = $this->actingAs($this->userAt('employee', $mySite))
            ->getJson('/form-data-query?site_id=' . $otherSite->id);

        $response->assertOk();
        $this->assertNull($response->json('data'));
    }

    /** NEW-3 */
    public function test_deleting_a_user_who_filed_logs_succeeds_and_keeps_the_logs(): void
    {
        $site = Site::factory()->create();
        $employee = $this->userAt('employee', $site);

        $log = FormData::factory()->create([
            'site_id' => $site->id,
            'resident_id' => Resident::factory()->create(['site_id' => $site->id])->id,
            'created_by' => $employee->id,
        ]);

        $this->actingAs($this->userAt('supervisor', $site))
            ->delete('/admin/user/' . $employee->id)
            ->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $employee->id]);
        $this->assertDatabaseHas('form_data', ['id' => $log->id, 'created_by' => null]);
    }

    /** NEW-3 */
    public function test_deleting_a_site_with_logs_is_refused_with_a_message(): void
    {
        $site = Site::factory()->create();
        FormData::factory()->create([
            'site_id' => $site->id,
            'resident_id' => Resident::factory()->create(['site_id' => $site->id])->id,
        ]);

        $this->actingAs($this->userAt('admin'))
            ->delete('/admin/sites/' . $site->id)
            ->assertRedirect(route('admin.resident'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('sites', ['id' => $site->id]);
    }

    public function test_deleting_a_site_without_logs_still_works(): void
    {
        $site = Site::factory()->create();

        $this->actingAs($this->userAt('admin'))
            ->delete('/admin/sites/' . $site->id)
            ->assertRedirect(route('admin.resident'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('sites', ['id' => $site->id]);
    }

    /** NEW-3 */
    public function test_deleting_a_resident_with_logs_is_refused_with_a_message(): void
    {
        $site = Site::factory()->create();
        $resident = Resident::factory()->create(['site_id' => $site->id]);
        FormData::factory()->create(['site_id' => $site->id, 'resident_id' => $resident->id]);

        $this->actingAs($this->userAt('admin'))
            ->delete('/admin/residents/' . $resident->id)
            ->assertSessionHas('error');

        $this->assertDatabaseHas('residents', ['id' => $resident->id]);
    }

    /** NEW-4 */
    public function test_director_is_not_offered_the_sites_and_residents_link(): void
    {
        $this->actingAs($this->userAt('director'))
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Sites and Residents');
    }

    public function test_admin_is_still_offered_the_sites_and_residents_link(): void
    {
        $this->actingAs($this->userAt('admin'))
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Sites and Residents');
    }

    /** NEW-5 */
    public function test_a_log_cannot_be_filed_against_another_sites_resident(): void
    {
        $mySite = Site::factory()->create();
        $otherSite = Site::factory()->create();
        $foreignResident = Resident::factory()->create(['site_id' => $otherSite->id]);

        $this->actingAs($this->userAt('employee', $mySite))
            ->postJson('/form/data', [
                'employee_type' => 'mcls',
                'mcls_name' => 'Casey Jones',
                'mcls_email' => 'casey@multiculturalcls.org',
                'resident_id' => $foreignResident->id,
                'shift' => 'morning',
                'adls' => 'x',
                'medical' => 'x',
                'behavior' => 'x',
                'log_date' => now()->toDateString(),
            ])
            ->assertStatus(422);

        $this->assertDatabaseCount('form_data', 0);
    }

    /** NEW-5 */
    public function test_editing_a_log_cannot_move_it_to_another_sites_resident(): void
    {
        $site = Site::factory()->create();
        $otherSite = Site::factory()->create();
        $resident = Resident::factory()->create(['site_id' => $site->id]);
        $foreignResident = Resident::factory()->create(['site_id' => $otherSite->id]);

        $log = FormData::factory()->create([
            'site_id' => $site->id,
            'resident_id' => $resident->id,
        ]);

        $this->actingAs($this->userAt('supervisor', $site))
            ->put('/log/data/' . $log->id, [
                'employee_type' => 'mcls',
                'log_date' => now()->toDateString(),
                'shift' => 'morning',
                'site_id' => $site->id,
                'resident_id' => $foreignResident->id,
            ])
            ->assertSessionHasErrors('resident_id');

        $this->assertSame($resident->id, $log->fresh()->resident_id);
    }
}
