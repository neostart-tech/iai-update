<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FraisInscription extends Model
{
    use HasFactory;
    protected $primaryKey = "id";
    protected $guarded = ["id"];
}
