<?php
namespace App\Jobs;
use App\Http\Controllers\ReleveController;
use App\Notifications\ReleveGroupePretNotification;
use Cache;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Group;
use Illuminate\Support\Facades\Cache as FacadesCache;
use Illuminate\Support\Facades\Storage as FacadesStorage;
use Notification;
use Storage;


class GenererRelevesGroupeJob implements ShouldQueue
{
   use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

     protected $group;
    protected $userId;

    public function __construct(Group $group, $userId)
    {
        $this->group = $group;
        $this->userId = $userId;
    }

    public function handle()
    {
        $controller = new  ReleveController();
        $etudiants = $this->group->etudiants;
        $releves = [];

        foreach ($etudiants as $etudiant) {
            $response = $controller->genererReleve($etudiant->id);
            $data = $response->getData(true);
            $releves[] = $data;
        }

        $pdf = Pdf::loadView('releves.index', [
            'groupe' => $this->group,
            'releves' => $releves,
        ])->setPaper('A4');

        $fileName = 'releve_' . $this->group->id . '_' . time() . '.pdf';
        $path = 'temp/' . $fileName;

        FacadesStorage::disk('local')->put($path, $pdf->output());

        // On stocke le nom dans le cache 10 minutes
        FacadesCache::put('releve_pdf_' . $this->userId, $fileName, now()->addMinutes(10));
    
    }
}
