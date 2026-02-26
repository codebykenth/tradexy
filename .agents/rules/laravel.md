---
trigger: always_on
---

Laravel 12 project – follow modern best practices + these rules:

ARCHITECTURE
- Thin controllers: coordinate only.
- Logic → app/Actions/ or app/Services/.
- FormRequest for validation & auth.
- Prefer Actions over fat models/controllers.
- Repositories only if multi-source data.

SECURITY (extra strict)
- Never env('…') – config() only.
- Private storage for sensitive files.
- Policies/Gates for authorization.
- No direct vendor/ or sensitive path exposure.

PERFORMANCE / SCALE
- Eager load + withCount().
- Queue heavy work.
- Cache aggressively (routes, config, views).
- Stateless sessions (redis/db).

CODE QUALITY
- SOLID, single responsibility.
- Enums / value objects for domain.
- Interfaces for swappable services.
- Pest tests, >80% coverage on logic.

STACK
- Laravel + Javascript + Tailwind.
- Ziggy for typed routes.
- Server-side + partial reloads.

Always:
- Show imports.
- Use route names.
- Add comments for non-obvious code.
- Suggest: php artisan optimize, pint, test.

Priority: Security > Correctness > Readability > Performance.