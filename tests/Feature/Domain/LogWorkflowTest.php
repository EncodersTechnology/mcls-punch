<?php

namespace Tests\Feature\Domain;

use App\Models\FormData;
use App\Models\Resident;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The core resident-log and checklist screens, plus the crashes they used to throw.
 */
class LogWorkflowTest extends TestCase
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

    public function test_employee_can_submit_a_resident_log(): void
    {
        $site = Site::factory()->create();
        $resident = Resident::factory()->create(['site_id' => $site->id]);
        $employee = $this->userAt('employee', $site);

        $this->actingAs($employee)
            ->postJson('/form/data', [
                'employee_type' => 'mcls',
                'mcls_name' => 'Casey Jones',
                'mcls_email' => 'casey@multiculturalcls.org',
                'resident_id' => $resident->id,
                'shift' => 'morning',
                'adls' => 'ate well',
                'medical' => 'stable',
                'behavior' => 'calm',
                'log_date' => now()->toDateString(),
            ])
            ->assertCreated();

        $this->assertDatabaseHas('form_data', [
            'resident_id' => $resident->id,
            'site_id' => $site->id,
            'created_by' => $employee->id,
        ]);
    }

    /** BUG-5 */
    public function test_submitting_a_log_without_a_site_assignment_is_rejected_cleanly(): void
    {
        $resident = Resident::factory()->create();

        $this->actingAs($this->userAt('director'))
            ->postJson('/form/data', [
                'employee_type' => 'mcls',
                'mcls_name' => 'Casey Jones',
                'mcls_email' => 'casey@multiculturalcls.org',
                'resident_id' => $resident->id,
                'shift' => 'morning',
                'adls' => 'ate well',
                'medical' => 'stable',
                'behavior' => 'calm',
                'log_date' => now()->toDateString(),
            ])
            ->assertStatus(422)
            ->assertJson(['status' => false]);
    }

    /** BUG-5 */
    public function test_site_log_form_without_a_site_assignment_is_forbidden_not_fatal(): void
    {
        $this->actingAs($this->userAt('director'))
            ->get('/employee/log/form')
            ->assertForbidden();
    }

    public function test_employee_can_open_the_site_log_form(): void
    {
        $site = Site::factory()->create();
        $this->seed(\Database\Seeders\XwalkSeeder::class);

        $this->actingAs($this->userAt('employee', $site))
            ->get('/employee/log/form')
            ->assertOk();
    }

    /** BUG-3 */
    public function test_siteadmin_can_open_the_admin_log_page(): void
    {
        $site = Site::factory()->create();
        FormData::factory()->create([
            'site_id' => $site->id,
            'resident_id' => Resident::factory()->create(['site_id' => $site->id])->id,
        ]);

        $this->actingAs($this->userAt('siteadmin'))
            ->get('/admin/log/data')
            ->assertOk();
    }

    public function test_admin_can_open_the_admin_log_page(): void
    {
        $this->actingAs($this->userAt('admin'))
            ->get('/admin/log/data')
            ->assertOk();
    }

    public function test_employee_log_list_only_shows_their_own_site(): void
    {
        $mySite = Site::factory()->create();
        $otherSite = Site::factory()->create();

        FormData::factory()->create([
            'site_id' => $mySite->id,
            'resident_id' => Resident::factory()->create(['site_id' => $mySite->id])->id,
            'mcls_name' => 'Mine Visible',
        ]);
        FormData::factory()->create([
            'site_id' => $otherSite->id,
            'resident_id' => Resident::factory()->create(['site_id' => $otherSite->id])->id,
            'mcls_name' => 'Hidden Elsewhere',
        ]);

        $this->actingAs($this->userAt('employee', $mySite))
            ->get('/employee/log/data')
            ->assertOk()
            ->assertSee('Mine Visible')
            ->assertDontSee('Hidden Elsewhere');
    }

    public function test_site_checklist_page_loads(): void
    {
        $site = Site::factory()->create();
        $this->seed(\Database\Seeders\XwalkSeeder::class);

        $this->actingAs($this->userAt('supervisor', $site))
            ->get('/view/site/checklist')
            ->assertOk();
    }

    /** BUG-5 */
    public function test_checklist_submission_without_a_site_is_rejected_cleanly(): void
    {
        $this->seed(\Database\Seeders\XwalkSeeder::class);
        $taskId = \DB::table('xwalk_site_checklist_type')->value('id');

        $this->actingAs($this->userAt('director'))
            ->post('/store/site/checklist', [
                'site_checklist_ids' => [$taskId],
                'mon_bool' => 1,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    /** BUG-6: the previous-Saturday branch used to corrupt the week window and reference
     *  an undefined variable. */
    public function test_checklist_submission_records_the_previous_saturday_entry(): void
    {
        $site = Site::factory()->create();
        $this->seed(\Database\Seeders\XwalkSeeder::class);
        $taskIds = \DB::table('xwalk_site_checklist_type')->limit(2)->pluck('id')->all();

        $this->actingAs($this->userAt('employee', $site))
            ->post('/store/site/checklist', [
                'site_checklist_ids' => $taskIds,
                'mon_bool' => 1,
                'prev_sat_bool' => 1,
                'staff_initial' => 'AB',
            ])
            ->assertRedirect();

        $weekStart = now()->startOfWeek(\Carbon\Carbon::SUNDAY);
        $prevSaturday = $weekStart->copy()->subDay()->toDateString();

        // One previous-week row per checklist id, all pointing at the same Saturday.
        $rows = \DB::table('site_checklist_data')
            ->where('site_id', $site->id)
            ->where('sat_bool', 1)
            ->get();

        $this->assertCount(count($taskIds), $rows);
        foreach ($rows as $row) {
            $this->assertSame(['sat' => $prevSaturday], json_decode($row->day_date_map, true));
        }
    }
}
