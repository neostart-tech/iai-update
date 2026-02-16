<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UrgentInfoResource;
use App\Models\UrgentInfo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

use function Laravel\Prompts\error;

class UrgentInfoController extends Controller
{
    public function index()
    {
        $items = UrgentInfo::query()->latest()->get();
        return UrgentInfoResource::collection($items);
        // return view('admin.urgent_infos.index', compact('items'));
    }

    public function show(UrgentInfo $urgent)
    {
        return new UrgentInfoResource($urgent);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'file_url' => ['nullable', 'url'],
            'file' => ['nullable', 'file', 'max:10240'], // 10MB
            'is_published' => ['nullable', 'boolean'],
        ]);

        // If both are empty, it's still allowed (can be informational). If both provided, prefer uploaded file.
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('urgent-infos', 'public');
            $data['file_path'] = $path;
        }

        $data['created_by'] = Auth::id();
        if (!empty($data['is_published'])) {
            $data['published_at'] = now();
        }

        $info = UrgentInfo::create($data);

        return new UrgentInfoResource($info);
        // return back()->with('success', 'Information urgente créée.');
    }

    public function update(Request $request, UrgentInfo $urgent)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'file_url' => ['nullable', 'url'],
            'file' => ['nullable', 'file', 'max:10240'],
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('urgent-infos', 'public');
            $data['file_path'] = $path;
        }

        $urgent->update($data);
        return new UrgentInfoResource($urgent);

        // return back()->with('success', 'Information urgente mise à jour.');
    }

    public function publish(UrgentInfo $urgent)
    {
        try {
            $urgent->is_published = true;
            $urgent->published_at = now();
            $urgent->save();
            return new UrgentInfoResource($urgent);
        } catch (Exception $e) {
            throw error($e->getMessage());
        }

        // return back()->with('success', 'Publication effectuée.');
    }

    public function unpublish(UrgentInfo $urgent)
    {
        $urgent->is_published = false;
        $urgent->published_at=null;
        $urgent->save();
        return new UrgentInfoResource($urgent);
        // return back()->with('success', 'Dépublication effectuée.');
    }

    public function destroy(UrgentInfo $urgent)
    {
        $urgent->delete();
        return new UrgentInfoResource($urgent);
        // return back()->with('success', 'Supprimé.');
    }
}
