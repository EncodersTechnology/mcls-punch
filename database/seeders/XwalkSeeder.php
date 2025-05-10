<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class XwalkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('xwalk_site_checklist_type')->insert([
            [
                'checklist_type' => 'DAY SHIFT CHECKLIST',
                'group_name' => 'MEDICATION',
                'task_name' => 'ADMINISTER MEDS AT MEDICATION TIMES',
            ],
            [
                'checklist_type' => 'DAY SHIFT CHECKLIST',
                'group_name' => 'MEDICATION',
                'task_name' => 'COMPLETE MAR SHEETS',
            ],
            [
                'checklist_type' => 'DAY SHIFT CHECKLIST',
                'group_name' => 'MEDICATION',
                'task_name' => 'MEDICATION INTAKE',
            ],
            [
                'checklist_type' => 'DAY SHIFT CHECKLIST',
                'group_name' => 'MEDICATION',
                'task_name' => 'CHECK FOR EXPIRED MED AND SUPPLIES (MONTHLY)',
            ],
            [
                'checklist_type' => 'DAY SHIFT CHECKLIST',
                'group_name' => 'MEDICATION',
                'task_name' => 'MAR SHEET CROSS CHECKED WITH MEDS (MONTHLY)',
            ],
            [
                'checklist_type' => 'DAY SHIFT CHECKLIST',
                'group_name' => 'INDIVIDUAL SUPPORTS',
                'task_name' => 'HYGIENE (AS NEEDED)',
            ],
            [
                'checklist_type' => 'DAY SHIFT CHECKLIST',
                'group_name' => 'INDIVIDUAL SUPPORTS',
                'task_name' => 'REVIEW ACTIVITY SCHEDULE FOR THE DAY',
            ],
            [
                'checklist_type' => 'DAY SHIFT CHECKLIST',
                'group_name' => 'INDIVIDUAL SUPPORTS',
                'task_name' => 'COMPLETE THE VISUAL SCHEDULE FOR THE DAY',
            ],
            [
                'checklist_type' => 'DAY SHIFT CHECKLIST',
                'group_name' => 'INDIVIDUAL SUPPORTS',
                'task_name' => 'PRIVACY LOCKS ENGAGED',
            ],
            [
                'checklist_type' => 'DAY SHIFT CHECKLIST',
                'group_name' => 'INDIVIDUAL SUPPORTS',
                'task_name' => 'DAILY WALKS',
            ],
            [
                'checklist_type' => 'DAY SHIFT CHECKLIST',
                'group_name' => 'CLEANING/COMPLIANCE',
                'task_name' => 'EMPTY ALL GARBAGE, RECYCLING & GREEN BIN',
            ],
            [
                'checklist_type' => 'DAY SHIFT CHECKLIST',
                'group_name' => 'CLEANING/COMPLIANCE',
                'task_name' => 'TAKE OUT GARBAGE',
            ],
            [
                'checklist_type' => 'DAY SHIFT CHECKLIST',
                'group_name' => 'CLEANING/COMPLIANCE',
                'task_name' => 'CLEAN KITCHEN AFTER MEAL PREP',
            ],
            [
                'checklist_type' => 'DAY SHIFT CHECKLIST',
                'group_name' => 'CLEANING/COMPLIANCE',
                'task_name' => 'WIPE DOWN TABLES AND CHAIRS',
            ],
            [
                'checklist_type' => 'DAY SHIFT CHECKLIST',
                'group_name' => 'CLEANING/COMPLIANCE',
                'task_name' => 'CLEAN FLOORS AROUND DINING TABLE',
            ],
            [
                'checklist_type' => 'DAY SHIFT CHECKLIST',
                'group_name' => 'CLEANING/COMPLIANCE',
                'task_name' => 'LOAD AND UNLOAD DISHWASHER',
            ],
            [
                'checklist_type' => 'DAY SHIFT CHECKLIST',
                'group_name' => 'CLEANING/COMPLIANCE',
                'task_name' => 'COMPLETE SANITIZING SCHEDULE',
            ],
            [
                'checklist_type' => 'DAY SHIFT CHECKLIST',
                'group_name' => 'DATA',
                'task_name' => 'FILL OUT DATA',
            ],
            [
                'checklist_type' => 'DAY SHIFT CHECKLIST',
                'group_name' => 'DATA',
                'task_name' => 'COMPLETE LOG BOOK (TEMPERATURE CHECKS)',
            ],
            [
                'checklist_type' => 'DAY SHIFT CHECKLIST',
                'group_name' => 'DATA',
                'task_name' => 'UPDATE FAMILIES (AS NEEDED)',
            ],
            [
                'checklist_type' => 'DAY SHIFT CHECKLIST',
                'group_name' => 'DATA',
                'task_name' => 'ORGANIZE BINDERS',
            ],
            [
                'checklist_type' => 'DAY SHIFT CHECKLIST',
                'group_name' => 'STAFF INITIAL',
                'task_name' => 'STAFF INITIAL',
            ],
            [
                'checklist_type' => 'NIGHT SHIFT CHECKLIST',
                'group_name' => 'KITCHEN/COMPLIANCE',
                'task_name' => 'CLEAN REFRIGERATOR (WEEKLY)',
            ],
            [
                'checklist_type' => 'NIGHT SHIFT CHECKLIST',
                'group_name' => 'KITCHEN/COMPLIANCE',
                'task_name' => 'ASSURE THAT FOOD IN FRIDGE IS LABELLED & DATED (WEEKLY)',
            ],
            [
                'checklist_type' => 'NIGHT SHIFT CHECKLIST',
                'group_name' => 'KITCHEN/COMPLIANCE',
                'task_name' => 'CHECK FRIDGE FOR EXPIRED FOOD (WEEKLY)',
            ],
            [
                'checklist_type' => 'NIGHT SHIFT CHECKLIST',
                'group_name' => 'KITCHEN/COMPLIANCE',
                'task_name' => 'UNLOAD DISHWASHER',
            ],
            [
                'checklist_type' => 'NIGHT SHIFT CHECKLIST',
                'group_name' => 'KITCHEN/COMPLIANCE',
                'task_name' => 'CLEAN MICROWAVE',
            ],
            [
                'checklist_type' => 'NIGHT SHIFT CHECKLIST',
                'group_name' => 'KITCHEN/COMPLIANCE',
                'task_name' => 'CLEAN STOVE (WEEKLY)',
            ],
            [
                'checklist_type' => 'NIGHT SHIFT CHECKLIST',
                'group_name' => 'KITCHEN/COMPLIANCE',
                'task_name' => 'CUPBOARDS ORGANIZE/WIPE DOWN (WEEKLY)',
            ],
            [
                'checklist_type' => 'NIGHT SHIFT CHECKLIST',
                'group_name' => 'CLEANING/COMPLIANCE',
                'task_name' => 'LAUNDRY',
            ],
            [
                'checklist_type' => 'NIGHT SHIFT CHECKLIST',
                'group_name' => 'CLEANING/COMPLIANCE',
                'task_name' => 'CLEAN BATHROOM (SANITISE SINK, TOILET, SHOWERS)',
            ],
            [
                'checklist_type' => 'NIGHT SHIFT CHECKLIST',
                'group_name' => 'CLEANING/COMPLIANCE',
                'task_name' => 'SWEEP/MOP BATHROOM',
            ],
            [
                'checklist_type' => 'NIGHT SHIFT CHECKLIST',
                'group_name' => 'CLEANING/COMPLIANCE',
                'task_name' => 'SWEEP/MOP LIVING ROOM AREA',
            ],
            [
                'checklist_type' => 'NIGHT SHIFT CHECKLIST',
                'group_name' => 'CLEANING/COMPLIANCE',
                'task_name' => 'SPOT CLEAN ALL WALLS (WEEKLY)',
            ],
            [
                'checklist_type' => 'NIGHT SHIFT CHECKLIST',
                'group_name' => 'CLEANING/COMPLIANCE',
                'task_name' => 'CLEAN WINDOW LEDGES (WEEKLY)',
            ],
            [
                'checklist_type' => 'NIGHT SHIFT CHECKLIST',
                'group_name' => 'CLEANING/COMPLIANCE',
                'task_name' => 'CLEAN FRONT AREA',
            ],
            [
                'checklist_type' => 'NIGHT SHIFT CHECKLIST',
                'group_name' => 'CLEANING/COMPLIANCE',
                'task_name' => 'WIPE DOWN DOORS',
            ],
            [
                'checklist_type' => 'NIGHT SHIFT CHECKLIST',
                'group_name' => 'CLEANING/COMPLIANCE',
                'task_name' => 'SANITIZE ALL DOOR KNOBS',
            ],
            [
                'checklist_type' => 'NIGHT SHIFT CHECKLIST',
                'group_name' => 'DATA',
                'task_name' => 'COMPLETE DATA',
            ],
            [
                'checklist_type' => 'NIGHT SHIFT CHECKLIST',
                'group_name' => 'DATA',
                'task_name' => 'MAKE SURE AREA IS ORGANIZED (MEDICATION AREA)',
            ],
            [
                'checklist_type' => 'NIGHT SHIFT CHECKLIST',
                'group_name' => 'DATA',
                'task_name' => 'MAKE A LIST OF NEEDED SUPPLIES',
            ],
            [
                'checklist_type' => 'NIGHT SHIFT CHECKLIST',
                'group_name' => 'STAFF INITIAL',
                'task_name' => 'STAFF INITIAL',
            ]

            ]);
    }
}
