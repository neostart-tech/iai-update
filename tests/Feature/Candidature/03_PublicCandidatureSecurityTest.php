<?php

namespace Tests\Feature\Candidature;

use Tests\TestCase;

class PublicCandidatureSecurityTest extends TestCase
{
    public function test_confirmation_button_wording_is_corrected(): void
    {
        $response = $this->get('/candidatures/faire-mon-depot');

        $response->assertOk();
        $response->assertSee("Oui, je suis d'accord", false);
        $response->assertDontSee("Oui, je susi d'accord", false);
    }

    public function test_public_home_links_use_the_public_frontend_url(): void
    {
        $create = file_get_contents(resource_path('views/candidatures/create.blade.php'));
        $identite = file_get_contents(resource_path('views/candidatures/_identite.blade.php'));
        $config = file_get_contents(config_path('app.php'));
        $envExample = file_get_contents(base_path('.env.example'));

        $this->assertStringContainsString('public_frontend_url', $config);
        $this->assertStringContainsString('PUBLIC_FRONTEND_URL', $envExample);
        $this->assertStringContainsString('$publicFrontendUrl', $create);
        $this->assertStringContainsString('$publicFrontendUrl', $identite);
        $this->assertStringNotContainsString('route(\'home\')', $create);
    }

    public function test_no_database_query_or_controller_logic_is_embedded_in_public_views(): void
    {
        $viewFiles = [
            resource_path('views/candidatures/create.blade.php'),
            resource_path('views/candidatures/_identite.blade.php'),
            resource_path('views/candidatures/_docs.blade.php'),
            resource_path('views/candidatures/_tuteur.blade.php'),
            resource_path('views/candidatures/_styles.blade.php'),
            resource_path('views/candidatures/merci.blade.php'),
        ];

        foreach ($viewFiles as $file) {
            $content = file_get_contents($file);

            $this->assertStringNotContainsString('DB::', $content, $file);
            $this->assertStringNotContainsString('Schema::', $content, $file);
            $this->assertStringNotContainsString('Candidature::', $content, $file);
        }
    }

    public function test_external_blank_links_are_protected(): void
    {
        $tuteur = file_get_contents(resource_path('views/candidatures/_tuteur.blade.php'));

        if (str_contains($tuteur, 'target="_blank"')) {
            $this->assertStringContainsString('rel="noopener noreferrer"', $tuteur);
        }
    }
}
