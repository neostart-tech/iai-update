<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Lors de la sauvegarde, si is_photo est true, on désactive les autres
    protected static function booted()
    {
        static::saving(function ($documentType) {
            if ($documentType->is_photo) {
                DocumentType::where('id', '!=', $documentType->id)->update(['is_photo' => false]);
            }
        });
    }

    public function documentRequirements()
    {
        return $this->hasMany(DocumentRequirement::class);
    }
}
