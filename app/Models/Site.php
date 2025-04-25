<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;
    protected $fillable = ['name'];

    public function residents()
    {
        return $this->hasMany(Resident::class);
    }

    public function siteUsers()
    {
        return $this->hasMany(SiteUsers::class);
    }

    public function users()
    {
        return $this->hasManyThrough(User::class, SiteUsers::class, 'site_id', 'id', 'id', 'user_id');
    }
}
