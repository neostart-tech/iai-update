<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentUvStatus extends Model
{
    protected $fillable = [
        'etudiant_id','uv_id','group_id','total_sessions','absences_count','absence_rate','warning_level','blocked'
    ];
}
