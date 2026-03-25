<?php
namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\EmploiDuTempController;

class EmploiDuTempsImport implements ToCollection
{
    protected $controller;

    public function __construct()
    {
        $this->controller = new EmploiDuTempController();
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {

            if ($index == 0) continue;

            try {

        
                $request = new Request([
                    'debut' => $row[0] . ' ' . $row[1],
                    'fin' => $row[0] . ' ' . $row[2],

                  
                    'uv_id' => $row[3],     
                    'type' => $row[4],      
                    'salle' => $row[5],     
                    'teacher' => $row[6],   
                    'details' => $row[7] ?? null,

                    'grade' => $row[8] ?? null,
                ]);

              
                $this->controller->store($request);

            } catch (\Throwable $e) {
                continue;
            }
        }
    }
}