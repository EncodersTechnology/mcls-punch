<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteChecklistSetting extends Model
{
    protected $table = 'site_checklist_settings';
    protected $fillable = [
        'site_checklist_id',
        'site_id',
        'sun_enabled_bool',
        'mon_enabled_bool',
        'tue_enabled_bool',
        'wed_enabled_bool',
        'thu_enabled_bool',
        'fri_enabled_bool',
        'sat_enabled_bool',
        'created_by',
        'updated_by',
        'deleted_by',
        'is_deleted',
        'status',
    ];

    use HasFactory;
}
