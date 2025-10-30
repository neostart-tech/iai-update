<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{ClassCommitteeMember, Etudiant, Group};
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ClassCommitteeController extends Controller
{
    public function index(Request $request): View
    {
        $groupId = $request->integer('group_id');
        $groups = Group::orderBy('nom')->get(['id','nom']);
        $selectedGroup = $groupId ? Group::with('etudiants')->find($groupId) : null;
        $members = $selectedGroup
            ? ClassCommitteeMember::where('group_id', $selectedGroup->id)->orderBy('role')->get()
            : collect();
        return view('admin.committee.index', compact('groups','selectedGroup','members'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'group_id' => 'required|exists:groups,id',
            'etudiant_id' => 'required|exists:etudiants,id',
            'role' => 'required|in:delegue,delegue_adjoint,secretaire,secretaire_adjoint',
        ]);
        // enforce uniqueness per role per group
        ClassCommitteeMember::where('group_id', $data['group_id'])->where('role', $data['role'])->delete();
        ClassCommitteeMember::create($data + ['active' => true]);
        return back()->with('success', 'Membre de comité enregistré');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate(['id' => 'required|exists:class_committee_members,id']);
        ClassCommitteeMember::where('id', $request->id)->delete();
        return back()->with('success', 'Membre retiré');
    }
}
