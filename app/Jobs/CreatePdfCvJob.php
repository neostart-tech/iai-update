<?php

namespace App\Jobs;

use App\Models\Etudiant;
use App\Traits\FileManagementTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage as FacadesStorage;
use Illuminate\Support\Str;
use Storage;

class CreatePdfCvJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, FileManagementTrait;

	public function __construct(private readonly Etudiant $etudiant)
	{
	}

	public function handle(): void
{

    $content = $this->etudiant->getAttribute('cv') ?? '<html><body>Aucun contenu</body></html>';
    
    $fileName = Str::slug($this->etudiant->nom.' '.$this->etudiant->prenom).'-'.uniqid().'.pdf';
    $filePath = 'cvs/'.$fileName;
    
   
    FacadesStorage::disk('public')->makeDirectory('cvs');

    try {
      
        $pdf = Pdf::loadHTML($content);
        FacadesStorage::disk('public')->put($filePath, $pdf->output());
        
        $album = $this->etudiant->album;
        if ($album->cv && FacadesStorage::disk('public')->exists($album->cv)) {
            FacadesStorage::disk('public')->delete($album->cv);
        }
      
        $album->update(['cv' => $filePath]);
        
    } catch (\Exception $e) {
        logger()->error('Erreur génération PDF : '.$e->getMessage());
        throw new \RuntimeException('Échec de la génération du PDF: '.$e->getMessage());
    }
}
}
