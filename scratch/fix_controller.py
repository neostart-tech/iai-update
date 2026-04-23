
with open(r'c:\Users\USER\Desktop\Edu-Manager\iai-update\app\Http\Controllers\ReleveNoteController.php', 'r') as f:
    lines = f.readlines()

# Remove the last two lines (the closing braces)
# But wait, let's be careful.
# Line 155 is } and 156 is }
# Let's find the last occurrence of } and remove everything after.

content = "".join(lines)
last_brace_index = content.rfind('}')
if last_brace_index != -1:
    # Find the second to last brace (the one closing bulkGenerate)
    # No, let's just find the last brace of the class.
    # Actually, the file ends with:
    #     }
    # }
    
    # We want to insert BEFORE the very last } (the class closing)
    # But wait, we want to replace the whole end.
    
    new_content = content[:last_brace_index].rstrip() + """

	public function getRelevesStatus(Request $request)
	{
		$releves = \\App\\Models\\ReleveNote::whereIn('etudiant_id', $request->student_ids)
			->where('periode_id', $request->periode_id)
			->with(['etudiant', 'anneeScolaire', 'periode'])
			->get();

		$status = [];
		foreach ($releves as $releve) {
			$status[$releve->etudiant_id] = [
				'exists' => true,
				'releve_id' => $releve->id,
				'date' => $releve->created_at->format('d/m/Y H:i'),
				'data' => $this->noteService->getReleveFormatted(
					$releve->etudiant,
					$releve->anneeScolaire,
					$releve->periode
				)
			];
		}

		return $status;
	}

	public function checkStatuses(Request $request)
	{
		return response()->json([
			'statuses' => $this->getRelevesStatus($request)
		]);
	}
}
"""
    with open(r'c:\Users\USER\Desktop\Edu-Manager\iai-update\app\Http\Controllers\ReleveNoteController.php', 'w') as f:
        f.write(new_content)
    print("Successfully updated ReleveNoteController.php")
else:
    print("Could not find closing brace")
