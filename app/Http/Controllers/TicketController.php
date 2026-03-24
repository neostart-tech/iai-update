<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{

    public function index()
    {
        $tickets = Ticket::with('ticketable')
            ->latest()
            ->get();

        return response()->json([
            'data' => $tickets
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string'
        ]);

        $user = Auth::user();

        $ticket = $user->tickets()->create([
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'open'
        ]);

        return response()->json([
            'message' => 'Ticket créé avec succès',
            'data' => $ticket
        ], 201);
    }


    public function show($id)
    {
        $ticket = Ticket::with('ticketable')->findOrFail($id);

        return response()->json([
            'data' => $ticket
        ]);
    }


    public function destroy($id)
    {
        $ticket = Ticket::findOrFail($id);

        $ticket->delete();

        return response()->json([
            'message' => 'Ticket supprimé avec succès'
        ]);
    }


    public function close($id)
    {
        $ticket = Ticket::findOrFail($id);

        $ticket->status = "closed";
        $ticket->save();

        return response()->json([
            'message' => 'Ticket fermé avec succès',
            'data' => $ticket
        ]);
    }

}