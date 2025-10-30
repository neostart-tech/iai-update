<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassCommitteeMember extends Model
{
    protected $fillable = ['group_id','etudiant_id','role','active'];
}
