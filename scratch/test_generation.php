<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $e = \App\Models\Etudiant::whereHas("etudiantGroups.group", function($q) { 
        $q->where("nom", "like", "%finance digitale%")->orWhere("nom", "like", "%l3%"); 
    })->first();

    if ($e) {
        echo "Found student: " . $e->nom . " " . $e->prenom . "\n";
        $p = \App\Models\Periode::first();
        if (!$p) {
            echo "No period found\n";
            exit;
        }
        
        $request = new \Illuminate\Http\Request();
        $request->merge(["student_ids" => [$e->id], "periode_id" => $p->id]);
        
        $ctrl = app(\App\Http\Controllers\ReleveNoteController::class);
        $res = $ctrl->bulkGenerate($request);
        
        echo "Response:\n";
        print_r(json_decode($res->getContent(), true));
    } else {
        echo "No student found.\n";
    }
} catch (\Exception $ex) {
    echo "Exception: " . $ex->getMessage() . "\n" . $ex->getTraceAsString();
}
