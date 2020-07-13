<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    //
    use SoftDeletes;

    public function owner(){

        return $this->belongsTo('\App\User', 'owner_id');
    }

    public function tasks(){

        return $this->hasMany('\App\Models\Task');
    }

    public function users(){

        return $this->belongsToMany('\App\User');
    }
}
