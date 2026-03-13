<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MessageAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'file_extension'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $appends = ['url', 'icon', 'formatted_size', 'preview_url', 'download_url'];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    // Obtenir l'URL complète du fichier
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }

    // URL de prévisualisation
    public function getPreviewUrlAttribute()
    {
        return route('messages.attachments.preview', [
            'conversation' => $this->message->conversation_id,
            'message' => $this->message_id,
            'attachment' => $this->id
        ]);
    }

    // URL de téléchargement
    public function getDownloadUrlAttribute()
    {
        return route('messages.attachments.download', [
            'conversation' => $this->message->conversation_id,
            'message' => $this->message_id,
            'attachment' => $this->id
        ]);
    }

    // Obtenir une icône selon le type de fichier
    public function getIconAttribute()
    {
        $extension = strtolower($this->file_extension);
        
        $icons = [
            'pdf' => 'fa-file-pdf',
            'doc' => 'fa-file-word',
            'docx' => 'fa-file-word',
            'xls' => 'fa-file-excel',
            'xlsx' => 'fa-file-excel',
            'ppt' => 'fa-file-powerpoint',
            'pptx' => 'fa-file-powerpoint',
            'jpg' => 'fa-file-image',
            'jpeg' => 'fa-file-image',
            'png' => 'fa-file-image',
            'gif' => 'fa-file-image',
            'mp4' => 'fa-file-video',
            'mp3' => 'fa-file-audio',
            'zip' => 'fa-file-archive',
            'rar' => 'fa-file-archive'
        ];

        return $icons[$extension] ?? 'fa-file';
    }

    // Vérifier si c'est une image
    public function getIsImageAttribute()
    {
        return strpos($this->mime_type, 'image/') === 0;
    }

    // Vérifier si c'est un PDF
    public function getIsPdfAttribute()
    {
        return $this->mime_type === 'application/pdf';
    }

    // Formater la taille du fichier
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}