<?php

namespace Tests\Feature\Candidature;

use Tests\TestCase;

class PublicCandidatureFrontendValidationTest extends TestCase
{
    public function test_submit_button_remains_clickable_but_explains_missing_fields(): void
    {
        $response = $this->get('/candidatures/faire-mon-depot');

        $response->assertOk();
        $response->assertSee('id="submit-help"', false);
        $response->assertSee('function showMissingRequirements()', false);
        $response->assertSee('Dossier incomplet', false);
        $response->assertSee('aria-disabled', false);
        $response->assertDontSee('submitButton.disabled', false);
    }

    public function test_required_fields_are_checked_across_all_steps(): void
    {
        $response = $this->get('/candidatures/faire-mon-depot');

        $response->assertOk();
        $response->assertSee('const requiredFields = candidatureForm.querySelectorAll', false);
        $response->assertSee('closest(\'.step-panel\')', false);
        $response->assertSee('change_tab(stepSelector)', false);
        $response->assertSee('missingField.focus', false);
    }

    public function test_dynamic_documents_trigger_submit_state_refresh(): void
    {
        $response = $this->get('/candidatures/faire-mon-depot');

        $response->assertOk();
        $response->assertSee('window.updateCandidatureSubmitState?.()', false);
        $response->assertSee('document.addEventListener(\'DOMContentLoaded\', updateDocumentsRequis)', false);
    }
}
