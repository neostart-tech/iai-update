<?php

namespace App\Services;

use Amrachraf6699\LaravelGeminiAi\Facades\GeminiAi;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    /**
     * Suggérer une note basée sur la réponse de l'étudiant
     */
    public function suggestGrade(string $question, string $studentResponse, float $maxPoints, ?string $correctionGuide = null): array
    {
        $prompt = "Tu es un professeur expert chargé de corriger un examen.
        
        Sujet/Question: \"$question\"
        Réponse de l'étudiant: \"$studentResponse\"
        Barème maximum: $maxPoints points.
        " . ($correctionGuide ? "Guide de correction: \"$correctionGuide\"" : "") . "
        
        Analyse la réponse de l'étudiant avec rigueur. 
        Donne une note sur $maxPoints et une courte justification expliquant pourquoi tu as donné cette note.
        Sois équitable mais précis.
        
        Réponds UNIQUEMENT au format JSON comme ceci:
        {
            \"points\": 15.5,
            \"justification\": \"L'étudiant a bien compris le concept mais a oublié de mentionner X...\"
        }";

        return $this->callGemini($prompt, 0.2);
    }

    /**
     * Générer des questions basées sur un sujet/contexte
     */
    public function generateQuestions(string $topic, int $count = 5, string $difficulty = 'intermédiaire', string $language = 'français'): array
    {
        $prompt = "Tu es un enseignant expert. Génère $count questions d'examen sur le sujet suivant : \"$topic\".
        Difficulté : $difficulty. Langue : $language.
        
        Pour chaque question, fournis :
        1. L'intitulé de la question.
        2. Le type (qcm_unique, texte_court ou texte_long).
        3. Si c'est un QCM, fournis 4 options dont une correcte.
        4. Le nombre de points suggéré.
        5. Un guide de correction rapide.
        
        Réponds UNIQUEMENT au format JSON comme ceci :
        {
            \"questions\": [
                {
                    \"content\": \"Quelle est la capitale de la France ?\",
                    \"type\": \"qcm_unique\",
                    \"points\": 2,
                    \"options\": [
                        {\"text\": \"Paris\", \"is_correct\": true},
                        {\"text\": \"Lyon\", \"is_correct\": false},
                        {\"text\": \"Marseille\", \"is_correct\": false},
                        {\"text\": \"Bordeaux\", \"is_correct\": false}
                    ],
                    \"correction_guide\": \"La réponse correcte est Paris.\"
                }
            ]
        }";

        return $this->callGemini($prompt);
    }

    /**
     * Obtenir de l'aide pour une question spécifique (leurres, reformulations, etc.)
     */
    public function getQuestionAssistance(string $currentContent, string $type, ?array $currentOptions = null): array
    {
        $optionsContext = $currentOptions ? "Options actuelles : " . json_encode($currentOptions) : "";
        
        $prompt = "Tu es un assistant pédagogique. Aide-moi à améliorer cette question d'examen :
        Contenu : \"$currentContent\"
        Type : $type
        $optionsContext
        
        Propose :
        1. Une version reformulée plus claire.
        2. Si c'est un QCM, suggère 3 distracteurs (mauvaises réponses) crédibles.
        3. Une explication de la réponse correcte.
        
        Réponds UNIQUEMENT au format JSON :
        {
            \"refined_content\": \"...\",
            \"suggested_distractors\": [\"d1\", \"d2\", \"d3\"],
            \"explanation\": \"...\"
        }";

        return $this->callGemini($prompt);
    }

    /**
     * Méthode générique pour appeler l'API Gemini via le package
     */
    private function callGemini(string $prompt, float $temperature = 0.4): array
    {
        try {
            $text = GeminiAi::generateText($prompt, [
                'generationConfig' => [
                    'temperature' => $temperature
                ]
            ]);

            if (empty($text)) {
                return ['success' => false, 'message' => "Réponse vide de l'IA."];
            }

            // Nettoyage Markdown JSON si nécessaire
            $text = preg_replace('/^```json\s*|```$/m', '', $text);
            $data = json_decode(trim($text), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Gemini JSON Decode Error: ' . json_last_error_msg() . ' | Content: ' . $text);
                return ['success' => false, 'message' => "Erreur de formatage de la réponse IA."];
            }

            return array_merge(['success' => true], $data ?? []);
        } catch (\Exception $e) {
            Log::error('Gemini Service Exception: ' . $e->getMessage());
            return ['success' => false, 'message' => "Erreur de communication avec l'IA : " . $e->getMessage()];
        }
    }
}
