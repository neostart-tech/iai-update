<?php

namespace App\Http\Controllers;

use App\Models\UrgentInfo;
use Illuminate\Http\Request;

class UrgentInfoPublicController extends Controller
{
    public function index()
    {
        $q = request('q');
        $query = UrgentInfo::query()
            ->where('is_published', true);

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%$q%")
                    ->orWhere('summary', 'like', "%$q%");
            });
        }

        $items = $query
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(9)
            ->withQueryString();

        // Petits indicateurs affichés dans le hero — calculés sur l'ensemble des
        // publications actives, pas seulement la page courante.
        $published = UrgentInfo::where('is_published', true);
        $stats = [
            'total' => (clone $published)->count(),
            'this_week' => (clone $published)->where('published_at', '>=', now()->subWeek())->count(),
            'attachments' => (clone $published)->get()->sum(fn ($u) => count($u->attachments ?? [])),
        ];

        return view('pages.infourgent', compact('items', 'stats'));
    }
}
