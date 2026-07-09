# Tests de robustesse — candidature publique Laravel

Cette suite vise `/candidatures/faire-mon-depot` sans modifier la logique métier.

- `01_PublicCandidatureSmokeTest.php` : accessibilité, routes, rendu principal.
- `02_PublicCandidatureFrontendValidationTest.php` : validation frontend et erreurs UX.
- `03_PublicCandidatureSecurityTest.php` : CSRF, absence de fuite de config, wording.
- `04_PublicCandidatureFragilityTest.php` : isolation du style, partial présentationnel, cache de vues.

Commandes :

```bash
php artisan test --filter PublicCandidature
php artisan test tests/Feature/Candidature
```
