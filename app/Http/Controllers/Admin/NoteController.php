<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\NotesPublicationJob;
use App\Models\{Etudiant, Evaluation};
use App\Models\Note;
use App\Models\NoteVariation;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EvaluationNotesExport;
use App\Http\Resources\EvaluationResource;
use App\Http\Resources\NoteResource;

class NoteController extends Controller
{
	public function evaluationNotesIndex(Evaluation $evaluation)
	{
		if (!$evaluation->getAttribute('has_anonymat')) {
			$this->generateFicheDeNote($evaluation);
		}

		$notes = $evaluation->notes()->with(['etudiant:id,slug,matricule,nom,prenom'])->get();
		//		dd(
		//			!$evaluation->getAttribute('correction_submission_date') &&
		//			today()->isBefore($evaluation->getAttribute('correction_end_date'))
		//		);
		$correctionEnable = !$evaluation->getAttribute('correction_submission_date') && today()->isBefore($evaluation->getAttribute('correction_end_date'));
		// return view('admin.evaluations.notes.index', compact('notes', 'evaluation', 'correctionEnable'));
		return response()->json(['notes' => NoteResource::collection($notes), "correctionEnable" => $correctionEnable]);
		// TODO Faire de cette méthode une action
	}

	private function generateFicheDeNote(Evaluation $evaluation): void
	{
		$notes = collect();
		$evaluation->group->etudiants->each(function (Etudiant $etudiant) use ($notes, $evaluation) {
			$notes->add([
				'unite_valeur_id' => $evaluation->getAttribute('unite_valeur_id'),
				'etudiant_id' => $etudiant->getAttribute('id'),
				...injectAnneeScolaireId(),
				'anonymat' => $this->generateAnonymat()
			]);
		});
		$evaluation->update(['has_anonymat' => true]);
		$evaluation->notes()->createMany($notes);
	}

	private function generateAnonymat(): string
	{
		// 2 uppercase letters + 3 digits, e.g., AB123
		$letters = Str::upper(Str::random(2));
		$digits = str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
		return $letters . $digits;
	}

	// public function storeNotes(Request $request, Evaluation $evaluation)
	// {
	// 	$etudiants = $request->collect('etudiants');
	// 	$etudiants = Etudiant::query()->whereIn('slug', $etudiants)->get();
	// 	$notes = $request->collect('notes');

	// 	$etudiants->each(function (Etudiant $etudiant, $key) use ($request, $evaluation, $notes) {
	// 		$note = $evaluation->notes()->firstWhere('etudiant_id', $etudiant->getAttribute('id'));
	// 		$raw = $notes->get($key);
	// 		if ($note === null) {
	// 			return;
	// 		}
	// 		if (is_string($raw) && strtoupper(trim($raw)) === 'R') {
	// 			$note->update(['notation' => 'R', 'note' => null]);
	// 			return;
	// 		}
	// 		$value = is_numeric($raw) ? (float) $raw : null;
	// 		if ($value !== null && $value >= 0 && $value <= 20) {
	// 			$note->update(['notation' => null, 'note' => $value]);
	// 		} else {
	// 			return __404("Des notes ont été ignorées car n'ayant pas une valeur valide (attendu 0-20 ou 'R')");
	// 		}
	// 	});

	// 	// successMsg("Notes enregistrées avec succès");
	// 	// return back();
	// 	return response()->json(['message' => "Notes enregistrées avec succès"]);
	// }



	public function storeNotes(Request $request, Evaluation $evaluation)
	{
		// if($evaluation->getAttribute('correction_submission_date') || today()->isAfter($evaluation->getAttribute('correction_end_date'))){
		// 	return __404("La période de correction de cette évaluation est terminée. Vous ne pouvez plus modifier les notes.");
		// }
		$requestSlugs = collect($request->input('etudiants', []));
		$requestNotes = collect($request->input('notes', []));

		// Charger tous les étudiants et indexer par slug
		$etudiantsMap = Etudiant::whereIn('slug', $requestSlugs->all())->get()->keyBy('slug');

		$requestSlugs->values()->each(function ($slug, $key) use ($etudiantsMap, $requestNotes, $evaluation) {
			$etudiant = $etudiantsMap->get($slug);
			if (! $etudiant) return;

			$note = $evaluation->notes()->firstWhere('etudiant_id', $etudiant->id);
			if (! $note) return;

			$raw = $requestNotes->get($key);

			if (is_string($raw) && strtoupper(trim($raw)) === 'R') {
				$note->update(['notation' => 'R', 'note' => null]);
				return;
			}

			$value = is_numeric($raw) ? (float) $raw : null;
			if ($value !== null && $value >= 0 && $value <= 20) {
				$note->update(['notation' => null, 'note' => $value]);
			}
		});

		return response()->json(['message' => 'Notes enregistrées avec succès']);
	}

	public function ChangeNotes(Request $request, Evaluation $evaluation)
	{
		$noteId = (int) $request->noteid;

		$note = Note::query()->find($noteId);

		// NoteVariation::firstOrCreate([
		// 	'note_id' => $noteId,
		// 	'from' => $note->getAttribute('note'),
		// 	'to' => (float) $request->newnote,
		// 	'motif' => $request->motif,
		// 	'user_id' => auth()->user()->getAttribute('id'),	
		// 	'annee_scolaire_id' => injectAnneeScolaireId(),
		// ]);

		$note->update(['note' => (float) $request->newnote]);
		// successMsg("Note modifiée avec succès");
		// return back();
		return response()->json(['message' => "Note modifiée avec succès"]);
	}

	public function publishNotes(Evaluation $evaluation)
	{
		if ($evaluation->fin < now()) {
			// warningMsg("Impossible de publier les notes : la période de l'évaluation est déjà terminée.");
			// return back();
			// return response()->json(['message' => "Impossible de publier les notes : la période de l'évaluation est déjà terminée."], 404);
			return __404("Impossible de publier les notes : la période de l'évaluation est déjà terminée.");
		}
		$evaluation->update(['correction_submission_date' => now()]);
		NotesPublicationJob::dispatch($evaluation);

		// successMsg('Publication des notes en cours ...');
		// return back();
		return response()->json(['message' => "Publication des notes en cours ..."]);
	}

	public function export(Evaluation $evaluation)
	{
	
		if ($evaluation->correction_submission_date === null) {
			return __404("Impossible d'exporter les notes : la fiche de notes n'a pas été générée pour cette évaluation.");
		}
		// return __404("Impossible d'exporter les notes : la fiche de notes n'a pas été générée pour cette évaluation.");
		// return response()->json(['message' => "Impossible d'exporter les notes : la fiche de notes n'a pas été générée pour cette évaluation."], 404);
		$filename = 'notes_' . $evaluation->matiere->getAttribute('code') . '_' . now()->format('Ymd_His') . '.xlsx';
		return Excel::download(new EvaluationNotesExport($evaluation), $filename);
	}
}
