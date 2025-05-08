<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormData extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_type',
        'mcls_name',
        'mcls_email',
        'agency_name',
        'agency_employee_name',
        'site_id',
        'shift',
        'resident_id',
        'log_date',
        'log_time',
        'adls',
        'medical',
        'behavior',
        'activities',
        'nutrition',
        'sleep',
        'notes',
        'temperature',
        'created_by'
    ];

    public function site(){
        return $this->belongsTo(Site::class);
    }

    public function resident(){
        return $this->belongsTo(Resident::class);
    }

    public function createdBy(){
        return $this->belongsTo(User::class, 'created_by');
    }
}
