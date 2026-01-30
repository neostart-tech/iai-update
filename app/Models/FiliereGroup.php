<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiliereGroup extends Model
{
    use HasFactory;
    protected $table = 'filiere_group';

    protected $fillable = [
        'filiere_id',
        'group_id',
    ];

    public function filiere()
    {
        return $this->belongsTo(Filiere::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
