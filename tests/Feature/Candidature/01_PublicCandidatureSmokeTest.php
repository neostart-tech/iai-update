<?php

namespace Tests\Feature\Candidature;

use Tests\TestCase;

class PublicCandidatureSmokeTest extends TestCase
{
    public function test_public_candidature_form_is_accessible(): void
    {
        $response = $this->get('/candidatures/faire-mon-depot');

        $response->assertOk();
        $response->assertSee('Dossier de candidature');
        $response->assertSee('id="candidature-form"', false);
        $response->assertSee('id="auth-2"', false);
        $response->assertSee('id="auth-3"', false);
        $response->assertSee('id="auth-4"', false);
        $response->assertSee('id="auth-5"', false);
    }

    public function test_ebenezer_dynamic_questionnaire_elements_are_preserved(): void
    {
        $response = $this->get('/candidatures/faire-mon-depot');

        $response->assertOk();
        $response->assertSee('const documentRequirements', false);
        $response->assertSee('id="documents-fields-container"', false);
        $response->assertSee('template id="tuteur-card-template"', false);
        $response->assertSee('name="niveau_id"', false);
        $response->assertSee('name="filiere_id"', false);
    }

    public function test_allowed_bac_series_are_visible(): void
    {
        $response = $this->get('/candidatures/faire-mon-depot');

        $response->assertOk();
        $response->assertSee('value="C"', false);
        $response->assertSee('value="D"', false);
        $response->assertSee('value="E"', false);
        $response->assertSee('value="F2"', false);
    }
}
