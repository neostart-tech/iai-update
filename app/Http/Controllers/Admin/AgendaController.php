<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\MonAgendaResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Agenda;

class AgendaController extends Controller
{
    public function index()
    {
       
        return view('admin.agenda.index');
    }

    public function getAgenda()
    {
        $evenements = Agenda::where('user_id', Auth::id())->get();

        return MonAgendaResource::collection($evenements);
    }

    public function store(Request $request)
    {

        $request->validate([
            'titre' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
        ]);

        Agenda::create([
            'user_id' => Auth::id(),
            'titre' => $request->titre,
            'description' => $request->description,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return back()->with('success', 'Événement créé avec succès');
    }

     public function update(Request $request,Agenda $agenda)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
        ]);

        $agenda->update([
            'user_id' => Auth::id(),
            'titre' => $request->titre,
            'description' => $request->description,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return new  MonAgendaResource($agenda);

    }

    public function destroy(Agenda $agenda)
    {
        $agenda->delete();
       return new  MonAgendaResource($agenda);
    }
}
