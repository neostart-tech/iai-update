<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LiveKitCourseInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $studentName;
    public string $courseTitle;
    public string $teacherName;
    public string $classeName;
    public string $courseUrl;

    public function __construct(string $studentName, string $courseTitle, string $teacherName, string $classeName, string $courseUrl)
    {
        $this->studentName = $studentName;
        $this->courseTitle = $courseTitle;
        $this->teacherName = $teacherName;
        $this->classeName = $classeName;
        $this->courseUrl = $courseUrl;
    }

    public function build()
    {
        return $this->subject("Invitation au cours en ligne : {$this->courseTitle}")
                    ->html("
            <div style='font-family: Arial, sans-serif; background-color: #f4f6f9; padding: 30px; border-radius: 8px;'>
                <div style='max-width: 600px; margin: 0 auto; background: #ffffff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);'>
                    <h2 style='color: #4f46e5; margin-top: 0;'>Bonjour {$this->studentName},</h2>
                    <p style='color: #374151; font-size: 15px; line-height: 1.6;'>
                        Votre enseignant <strong>{$this->teacherName}</strong> a démarré la classe virtuelle pour la matière :
                    </p>
                    <div style='background-color: #f3f4f6; padding: 15px; border-left: 4px solid #4f46e5; border-radius: 6px; margin: 20px 0;'>
                        <h3 style='margin: 0 0 5px 0; color: #111827;'>{$this->courseTitle}</h3>
                        <p style='margin: 0; color: #6b7280; font-size: 13px;'>Classe / Filière : {$this->classeName}</p>
                    </div>
                    <p style='color: #374151; font-size: 14px;'>
                        Cliquez sur le bouton ci-dessous pour rejoindre la visioconférence en direct :
                    </p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$this->courseUrl}' style='background-color: #4f46e5; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 30px; font-weight: bold; display: inline-block; font-size: 15px;'>Rejoindre le cours en ligne &rarr;</a>
                    </div>
                    <hr style='border: none; border-top: 1px solid #e5e7eb; margin-top: 30px;' />
                    <p style='color: #9ca3af; font-size: 12px; text-align: center; margin-bottom: 0;'>
                        Cet e-mail automatique a été envoyé par la plateforme Edu-Manager.
                    </p>
                </div>
            </div>
        ");
    }
}
