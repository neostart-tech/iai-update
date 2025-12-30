<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait FileManagementTrait
{
	private static string $disk = 'public';

	/**
 * @param Request $request
 * @param string $fileKey
 * @param string $folderName
 * @param string|null $fileName
 * @return string|null
 */
private function storeFile(Request $request, string $fileKey, string $folderName, string $fileName = null): ?string
{
    $file = $request->file($fileKey);
    
    // CORRECTION : Vérifier si le fichier existe
    if (!$file) {
        return null;
    }
    
    // CORRECTION : Gérer le cas où c'est un tableau
    if (is_array($file)) {
        if (isset($file[0]) && $file[0]) {
            $fileFullName = uniqid($fileName ?? '') . '.' . $file[0]->getClientOriginalExtension();
            return $file[0]->storeAs($folderName, $fileFullName, static::$disk);
        }
        return null;
    }
    
    $fileFullName = uniqid($fileName ?? '') . '.' . $file->getClientOriginalExtension();
    return $file->storeAs($folderName, $fileFullName, static::$disk);
}

/**
 * @param Request $request
 * @param string $fileKey
 * @param string $folderName
 * @param string $niveau
 * @param string $filePrefix
 * @return array
 */
private function storeMultipleFiles(Request $request, string $fileKey, string $folderName, string $niveau, string $filePrefix): array
{
    $paths = [];

    if (!$request->hasFile($fileKey)) {
        return $paths;
    }

    $files = $request->file($fileKey);
    
    // CORRECTION : Vérifier que c'est bien un tableau
    if (!is_array($files)) {
        return $paths;
    }

    foreach ($files as $index => $file) {
        if ($file) {
            $fileFullName = uniqid($filePrefix . '_' . $index . '_') . '.' . $file->getClientOriginalExtension();
            $paths[] = $file->storeAs($folderName . '/' . $niveau, $fileFullName, static::$disk);
        }
    }

    return $paths; 
}


	/**
	 * @param Request $request
	 * @param string $fileKey
	 * @param string $folderName
	 * @param string $fileOldName
	 * @param string|null $fileName
	 * @return string
	 */
	private function updateFile(Request $request, string $fileKey, string $folderName, string $fileOldName, string $fileName = null): string
	{
		$this->deleteFile($fileOldName);
		return $this->storeFile($request, $fileKey, $folderName, $fileName);
	}


	/**
	 * Supprime un fichier sur le disque 'public' sur le serveur
	 *
	 * @param string $fileName
	 * @return void
	 */
	private function deleteFile(string $fileName = ''): void
	{
		if (($fileSystem = Storage::disk(static::$disk))->exists($fileName)) {
			$fileSystem->delete($fileName);
		}
	}


	/**
	 * Retourne l'Url d'accès à un fichier sur le disque 'public' depuis le serveur
	 *
	 * @param string $file_name
	 * @return string
	 */
	private function getFileUrl(string $file_name): string
	{
		return Storage::disk(static::$disk)->url($file_name);
	}

	/**
	 * Déplace un fichier en le renommant puis retourne le chemin du nouvel emplacement
	 * @param string $oldLocation
	 * @param string $folderName
	 * @param string $fileNamePrefix
	 * @return string
	 */
	private function moveFile(string $oldLocation, string $folderName, string $fileNamePrefix = ''): string
	{
		$extension = '.' . Str::after($oldLocation, '.'); // .pdf
		$tempName = Str::before($oldLocation, '.') . '-temp'; // folder/file-name-temp
		$newLocation = $tempLocation = $tempName . $extension; // folder/file-name-temp.pdf

		if (Storage::disk(static::$disk)->copy($oldLocation, $tempLocation)) {
			if (Storage::disk(static::$disk)->move($tempLocation, $newLocation = $folderName . '/' . uniqid($fileNamePrefix) . $extension))
				return $newLocation;
		}
		return $newLocation;
	}

	/**
	 * Lance le téléchargement d'un fichier sur le serveur
	 * @param string $filePath
	 * @return StreamedResponse
	 */
	public function downloadFile(string $filePath): StreamedResponse
	{
		return Storage::disk(static::$disk)->download($filePath);
	}
}
