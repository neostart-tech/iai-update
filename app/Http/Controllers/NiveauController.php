<?php

namespace App\Http\Controllers;

use App\Http\Resources\NiveauResource;
use App\Models\Niveau;
use Illuminate\Http\Request;

class NiveauController extends Controller
{
    public function index(){
        return NiveauResource::collection(Niveau::all());
    }
}
