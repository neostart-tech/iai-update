<?php

namespace Tests\Feature\Candidature;

use Tests\TestCase;

class PublicCandidatureFragilityTest extends TestCase
{
    public function test_redesign_keeps_style_isolated_in_partial(): void
    {
        $create = file_get_contents(resource_path('views/candidatures/create.blade.php'));
        $style = file_get_contents(resource_path('views/candidatures/_styles.blade.php'));

        $this->assertStringContainsString("@include('candidatures._styles')", $create);
        $this->assertStringContainsString('.depot-topbar', $style);
        $this->assertStringContainsString('.stepper', $style);
        $this->assertStringContainsString('.submit-help', $style);
        $this->assertStringContainsString('.btn-refined.is-disabled', $style);
    }

    public function test_local_backups_are_ignored_by_git(): void
    {
        $this->assertStringContainsString('.backups/', file_get_contents(base_path('.gitignore')));
    }

    public function test_page_stays_accessible_after_view_cache_clear(): void
    {
        $this->artisan('view:clear')->assertExitCode(0);

        $response = $this->get('/candidatures/faire-mon-depot');

        $response->assertOk();
        $response->assertSee('Soumettre ma candidature');
    }

    public function test_routes_stay_registered(): void
    {
        $this->assertTrue(\Route::has('candidatures.create'));
        $this->assertTrue(\Route::has('candidatures.store'));
        $this->assertTrue(\Route::has('candidatures.merci'));
    }
}
