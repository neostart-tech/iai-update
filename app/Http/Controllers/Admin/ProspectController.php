<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProspectResource;
use App\Models\Prospect;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProspectController extends Controller
{
    public function index()
    {
        return ProspectResource::collection(Prospect::query()->orderByDesc('created_at')->get());
    }

    public function show(Prospect $prospect)
    {
        return new ProspectResource($prospect);
    }

    public function destroy(Prospect $prospect)
    {
        $prospect->delete();
        return new ProspectResource($prospect);
    }

    public function toggleStatus(Prospect $prospect)
    {
        $prospect->update(['status' => !$prospect->status]);
        return new ProspectResource($prospect);
    }

    public function countUnread(): JsonResponse
    {
        return response()->json([
            'count' => Prospect::query()->where('status', false)->count()
        ]);
    }
}
