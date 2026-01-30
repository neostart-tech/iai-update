<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentationAccess extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    const ROLE    = 'ROLE';
    const GROUPE  = 'GROUPE';
    const FILIERE = 'FILIERE';
    const NIVEAU  = 'NIVEAU';
}
