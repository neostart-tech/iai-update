<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BackupController extends Controller
{
    private function getBackupDir(): string
    {
        $dir = storage_path('app/backups');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Liste toutes les sauvegardes disponibles.
     */
    public function index()
    {
        $dir = $this->getBackupDir();
        $files = File::files($dir);

        $backups = collect($files)->map(function ($file) {
            $bytes = $file->getSize();
            $name = $file->getFilename();
            $isZip = str_ends_with($name, '.zip');

            return [
                'filename' => $name,
                'size_bytes' => $bytes,
                'size_formatted' => $this->formatBytes($bytes),
                'type' => $isZip ? 'Complet (Base + Fichiers)' : 'Base de données',
                'extension' => $file->getExtension(),
                'created_at' => date('Y-m-d H:i:s', $file->getMTime()),
                'timestamp' => $file->getMTime(),
            ];
        })->sortByDesc('timestamp')->values();

        return response()->json([
            'success' => true,
            'backups' => $backups,
        ]);
    }

    /**
     * Génère une nouvelle sauvegarde (DB ou complète).
     */
    public function generate(Request $request)
    {
        $type = $request->input('type', 'db'); // 'db' ou 'full'
        $dir = $this->getBackupDir();
        $timestamp = date('Y-m-d_H-i-s');

        try {
            if ($type === 'full' && class_exists('ZipArchive')) {
                $filename = "sauvegarde-complete-{$timestamp}.zip";
                $filePath = $dir . DIRECTORY_SEPARATOR . $filename;
                
                // 1. Générer d'abord le SQL temporaire
                $tempSqlPath = $dir . DIRECTORY_SEPARATOR . "temp-db-{$timestamp}.sql";
                $this->dumpDatabase($tempSqlPath);

                // 2. Créer l'archive ZIP
                $zip = new ZipArchive();
                if ($zip->open($filePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                    // Ajouter le dump SQL
                    $zip->addFile($tempSqlPath, "database.sql");

                    // Ajouter le dossier storage/app/public
                    $storagePublic = storage_path('app/public');
                    if (File::exists($storagePublic)) {
                        $files = File::allFiles($storagePublic);
                        foreach ($files as $file) {
                            $relativePath = 'storage/' . $file->getRelativePathname();
                            $zip->addFile($file->getRealPath(), $relativePath);
                        }
                    }
                    $zip->close();
                }

                // Supprimer le SQL temporaire
                if (File::exists($tempSqlPath)) {
                    File::delete($tempSqlPath);
                }
            } else {
                // Sauvegarde de la base de données uniquement
                $filename = "sauvegarde-bdd-{$timestamp}.sql";
                $filePath = $dir . DIRECTORY_SEPARATOR . $filename;
                $this->dumpDatabase($filePath);
            }

            // Logger l'action dans le journal d'activité
            if (function_exists('activity')) {
                activity()
                    ->useLog('http')
                    ->withProperties([
                        'method' => 'POST',
                        'path' => 'api/admin/backups/generate',
                        'subject_label' => $filename,
                    ])
                    ->log("Création de sauvegarde : " . ($type === 'full' ? 'Complet' : 'Base de données'));
            }

            return response()->json([
                'success' => true,
                'message' => "La sauvegarde '{$filename}' a été créée avec succès.",
                'filename' => $filename,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => "Erreur lors de la génération de la sauvegarde : " . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Télécharge une sauvegarde.
     */
    public function download(string $filename)
    {
        $filename = basename($filename);
        $path = $this->getBackupDir() . DIRECTORY_SEPARATOR . $filename;

        if (!File::exists($path)) {
            return response()->json([
                'success' => false,
                'message' => "Fichier de sauvegarde introuvable.",
            ], 404);
        }

        return response()->download($path, $filename);
    }

    /**
     * Supprime un fichier de sauvegarde.
     */
    public function destroy(string $filename)
    {
        $filename = basename($filename);
        $path = $this->getBackupDir() . DIRECTORY_SEPARATOR . $filename;

        if (!File::exists($path)) {
            return response()->json([
                'success' => false,
                'message' => "Le fichier spécifié n'existe pas.",
            ], 404);
        }

        File::delete($path);

        return response()->json([
            'success' => true,
            'message' => "Sauvegarde supprimée avec succès.",
        ]);
    }

    /**
     * Effectue le dump de la base de données (mysqldump avec validation ou fallback PDO pur).
     */
    private function dumpDatabase(string $outputPath): void
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");
        $successWithMysqldump = false;

        if ($driver === 'mysql') {
            $host = config("database.connections.{$connection}.host");
            $port = config("database.connections.{$connection}.port");
            $dbName = config("database.connections.{$connection}.database");
            $username = config("database.connections.{$connection}.username");
            $password = config("database.connections.{$connection}.password");

            // Tenter d'exécuter mysqldump via la ligne de commande si disponible
            $cmd = sprintf(
                'mysqldump --host=%s --port=%s --user=%s --password=%s --quick --single-transaction %s > %s 2>&1',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($dbName),
                escapeshellarg($outputPath)
            );

            $returnVar = null;
            $output = [];
            @exec($cmd, $output, $returnVar);

            if ($returnVar === 0 && File::exists($outputPath) && File::size($outputPath) > 500) {
                $content = File::get($outputPath);
                if (str_contains($content, 'INSERT INTO') || str_contains($content, 'CREATE TABLE')) {
                    if (!str_contains($content, 'mysqldump: [ERROR]') && !str_contains($content, 'command not found') && !str_contains($content, 'is not recognized')) {
                        $successWithMysqldump = true;
                    }
                }
            }
        }

        // Si mysqldump n'a pas produit de SQL complet avec données, exécuter le dumper PDO pur
        if (!$successWithMysqldump) {
            if (File::exists($outputPath)) {
                File::delete($outputPath);
            }
            $this->dumpDatabasePurePhp($outputPath);
        }
    }

    /**
     * Exportateur PDO MySQL pur pour générer un fichier .sql complet avec structure ET DONNÉES.
     */
    private function dumpDatabasePurePhp(string $filePath): void
    {
        $pdo = \DB::connection()->getPdo();
        $dbName = \DB::connection()->getDatabaseName();

        $handle = fopen($filePath, 'w+');
        if (!$handle) {
            throw new \RuntimeException("Impossible d'ouvrir le fichier de destination {$filePath}");
        }

        fwrite($handle, "-- Sauvegarde Base de Données Edu-Manager\n");
        fwrite($handle, "-- Base : {$dbName}\n");
        fwrite($handle, "-- Date : " . date('Y-m-d H:i:s') . "\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n\n");

        $tables = \DB::select('SHOW TABLES');
        $tableKey = "Tables_in_{$dbName}";

        foreach ($tables as $tableObj) {
            $table = $tableObj->$tableKey ?? current((array) $tableObj);
            if (!$table) {
                continue;
            }

            // Exclure les vues ou tables temporaires si non-tables
            try {
                $createTable = \DB::select("SHOW CREATE TABLE `{$table}`");
                $createSql = $createTable[0]->{'Create Table'} ?? null;

                if ($createSql) {
                    fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
                    fwrite($handle, $createSql . ";\n\n");
                }

                // Récupération et écriture des données
                $rows = \DB::table($table)->get();
                if ($rows->count() > 0) {
                    $firstRow = (array) $rows->first();
                    $columns = array_map(fn($col) => "`{$col}`", array_keys($firstRow));
                    $colString = implode(', ', $columns);

                    $chunks = array_chunk($rows->all(), 100);
                    foreach ($chunks as $chunk) {
                        $inserts = [];
                        foreach ($chunk as $row) {
                            $values = array_map(function ($val) use ($pdo) {
                                if (is_null($val)) {
                                    return 'NULL';
                                }
                                return $pdo->quote((string) $val);
                            }, (array) $row);

                            $inserts[] = "(" . implode(', ', $values) . ")";
                        }

                        if (!empty($inserts)) {
                            fwrite($handle, "INSERT INTO `{$table}` ({$colString}) VALUES\n" . implode(",\n", $inserts) . ";\n\n");
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Ignorer les erreurs d'une table/vue spécifique et continuer la boucle
                continue;
            }
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}
