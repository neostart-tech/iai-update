<?php

namespace App\Http\Controllers\Api\Support;

use App\Http\Controllers\Controller;
use App\Models\Support\SupportTicket;
use App\Models\Support\SupportCategory;
use App\Http\Resources\Support\TicketResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\Support\SupportTicketUpdated;

class TicketController extends Controller
{
    // Liste des tickets (selon le rôle)
    public function index(Request $request)
    {
        $user = $request->user();
        $query = SupportTicket::with(['category', 'assignedAgent', 'ticketable']);

        // Le Support (Informaticien) et les Admins voient tous les tickets
        // On vérifie par nom (plusieurs variantes possibles) avec LIKE pour être sûr
        $isStaff = $user->isInformaticien() || 
                   $user->roles()->where('nom', 'like', '%Admin%')->exists() ||
                   $user->roles()->where('nom', 'like', '%Directeur%')->exists();

        if (!$isStaff) {
            $query->where('ticketable_id', $user->id)
                  ->where('ticketable_type', get_class($user));
        }

        $tickets = $query->orderBy('created_at', 'desc')->get();
        
        return TicketResource::collection($tickets);
    }
    
    // Créer un ticket
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:support_categories,id',
            'priority' => 'required|in:low,medium,high,critical',
        ]);
        
        $user = $request->user();
        
        $ticket = new SupportTicket();
        $ticket->reference = $ticket->generateReference();
        $ticket->title = $request->title;
        $ticket->description = $request->description;
        $ticket->ticketable_type = get_class($user);
        $ticket->ticketable_id = $user->id;
        $ticket->category_id = $request->category_id;
        $ticket->priority = $request->priority;
        $ticket->save();
        
        return new TicketResource($ticket);
    }
    
    // Voir un ticket
    public function show(SupportTicket $ticket)
    {
        $user = request()->user();
        
        if (!$ticket->canView($user)) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }
        
        return new TicketResource($ticket->load(['category', 'messages.user', 'messages.attachments', 'assignedAgent', 'ticketable']));
    }
    
    // Assigner un ticket (réservé informaticien)
    public function assign(Request $request, SupportTicket $ticket)
    {
        $user = $request->user();

        // Vérifier si l'user est autorisé (Informaticien, Admin, Directeur)
        $isStaff = $user->isInformaticien() || 
                   $user->roles()->where('nom', 'like', '%Admin%')->exists() ||
                   $user->roles()->where('nom', 'like', '%Directeur%')->exists();

        if (!$isStaff) {
            return response()->json(['message' => 'Réservé au support'], 403);
        }
        
        $assignedTo = $request->assigned_to ?? $user->id;
        
        $ticket->assigned_to = $assignedTo;
        $ticket->status = 'in_progress';
        $ticket->save();
        
        broadcast(new SupportTicketUpdated($ticket))->toOthers();
        
        return new TicketResource($ticket->load(['assignedAgent', 'ticketable', 'category']));
    }
    
    // Changer le statut
    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $user = $request->user();
        
        $isStaff = $user->isInformaticien() || 
                   $user->roles()->where('nom', 'like', '%Admin%')->exists() ||
                   $user->roles()->where('nom', 'like', '%Directeur%')->exists();

        // Un étudiant ne peut que clore son propre ticket
        if (!$isStaff) {
            if ($ticket->ticketable_id !== $user->id || !in_array($request->status, ['closed', 'resolved'])) {
                return response()->json(['message' => 'Non autorisé'], 403);
            }
        }

        $request->validate([
            'status' => 'required|in:open,in_progress,waiting,resolved,closed'
        ]);
        
        $ticket->status = $request->status;
        
        if ($request->status === 'resolved') {
            $ticket->resolved_at = now();
        }
        
        if ($request->status === 'closed') {
            $ticket->closed_at = now();
        }
        
        $ticket->save();
        
        broadcast(new SupportTicketUpdated($ticket))->toOthers();
        
        return new TicketResource($ticket->load(['assignedAgent', 'ticketable', 'category']));
    }
    
    // Évaluer un ticket (après résolution)
    public function rate(Request $request, SupportTicket $ticket)
    {
        $user = $request->user();
        
        if ($ticket->ticketable_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }
        
        if ($ticket->status !== 'resolved') {
            return response()->json(['message' => 'Ce ticket n\'est pas encore résolu'], 422);
        }
        
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string'
        ]);
        
        $ticket->rating = $request->rating;
        $ticket->feedback = $request->feedback;
        $ticket->save();
        
        return new TicketResource($ticket);
    }
}