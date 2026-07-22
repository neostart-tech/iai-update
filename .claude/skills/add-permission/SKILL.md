---
name: add-permission
description: Checklist to wire a new feature/route into this project's dynamic permission system (DB-driven roles/permissions), so nothing is left ungated or silently broken. Use whenever a new mutating route, controller action, or admin UI button is added to iai-update/gestion-ecole.
---

This project migrated from a static, hardcoded frontend permission map to a **dynamic, database-driven** permission system (see memory `project_dynamic_permissions` for the full history). Every new feature must plug into it — this skill is the checklist so that never has to be re-explained.

## When to use this

Any time you add or touch:
- A new mutating backend route (POST/PUT/PATCH/DELETE) in `iai-update/routes/*.php`.
- A new admin-facing button/action in `gestion-ecole` that should be restricted by role.

Read-only routes (GET index/show) are **not** gated by convention in this project — only mutations. Don't add `can:` middleware to a plain listing/detail route unless explicitly asked; that's a deliberate, established choice (lower regression risk), not an oversight.

## Steps

1. **Pick a slug** following the existing convention: `{action}-{domain}`, where `action` ∈ `create|update|delete|view|publish|send|declare|duplicate` and `domain` is the kebab-case resource name (e.g. `create-filiere`, `duplicate-frais-scolarite`). Check `database/seeders/DynamicPermissionsMigrationSeeder.php` for the full existing catalog before inventing a new domain name — reuse an existing one if the feature is part of an existing domain.

2. **Add the permission** in `database/seeders/DynamicPermissionsMigrationSeeder.php`:
   - If it belongs to a domain that already has other slugs there, add it to the relevant array/loop.
   - If it's a genuinely new domain, add a new `Permission::firstOrCreate(['slug' => ...], ['nom' => ..., 'description' => ...])` call.
   - Decide if the pilot/full-access roles need it — they get it automatically via the `$all()` (all slugged permissions) sync for `informaticien`/`directeur-general-adjoint`/`directeur-general`. If a specific non-full-access role should also get it (e.g. a finance-only permission), add it to that role's explicit sync list.

3. **Wire the backend route** in the relevant `routes/*.php` file: `->middleware('can:your-slug')` on the mutating route only (store/update/destroy — not index/show). Watch out for:
   - `Route::resource(...)` can't take per-verb middleware — split into `->except([...])` + explicit routes if you need different slugs per verb (see `routes/api.php` negociations for the pattern).
   - Multiple routes hitting the same controller action (e.g. `POST /paiements/store` and `POST /paiements`) both need the middleware, or the second becomes a bypass.
   - Don't confuse similarly-named but distinct resources (e.g. `frais-scolarite` vs `frais-inscription` are different controllers — check which one before reusing a slug).

4. **Run the seeder**: `php artisan db:seed --class=DynamicPermissionsMigrationSeeder`. It's idempotent (safe to re-run).

5. **Run the audit command**: `php artisan permissions:audit`. It must report no "slug manquant sans Gate::define" errors. It will also tell you if you created a permission nobody will ever see gated on a route (orphaned) — expected for permissions intentionally deferred (document why), unexpected otherwise.

6. **Gate the frontend button** in `gestion-ecole`: wrap the button/link in `<Can action="your-slug">...</Can>` (component already exists, auto-imported). If the whole page should be restricted (not just a button), add the `waitForUserLoaded()` + `if (!can('slug')) router.replace('/unauthorized')` guard in `onMounted` — see `app/pages/roles/[slug]/permissions.vue` or `app/pages/journal-activite/liste.vue` for the exact pattern to copy.

7. **If the permission needs to show up in the Roles admin UI** for assignment: nothing extra to do — `RoleController::availablePermissions()` already returns every permission with a non-null slug automatically, and the permissions page groups by domain automatically (parsed from the slug's action prefix).

8. **Sanity-check dark mode** on any new/touched frontend page — see the dark-mode rules in `gestion-ecole/CLAUDE.md` (no CSS custom-property redefinition inside `:global(.dark)`, always two literal rules).

## Not in scope for this checklist

- Read-only route gating (deliberately out of scope, see above).
- The activity/audit log (`view-logs` permission, `spatie/laravel-activitylog`) — that's a separate, already-wired system; new mutating routes get logged automatically via the global `LogActivity` middleware, no per-route work needed.
