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
        'site',
        'shift',
        'resident_name',
        'log_date',
        'log_time',
        'adls',
        'medical',
        'behavior',
        'activities',
        'nutrition',
        'sleep',
        'notes',
    ];

    public function site(){
        return $this->belongsTo(Site::class);
    }

    public function resident(){
        return $this->belongsTo(Resident::class);
    }
}
