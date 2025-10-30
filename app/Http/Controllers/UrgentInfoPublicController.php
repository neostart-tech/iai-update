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
            ->paginate(5)
            ->withQueryString();

        return view('pages.infourgent', compact('items'));
    }
}
