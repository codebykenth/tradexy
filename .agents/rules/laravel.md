---
trigger: always_on
---

Laravel 12 trading journal project — follow these rules strictly:

## ARCHITECTURE

**Controllers (thin — coordinate only):**
- Max ~10 lines per public method: validate → delegate → redirect.
- Never put business logic in controllers.
- Use `findOrFail()` with ownership checks (`where('user_id', Auth::id())`).
- Use private helper methods to share logic between `store()` and `update()` (DRY).
- Use `$model->fill($data)->save()` instead of separate `create()`/`update()` when logic is shared.
- Return typed `RedirectResponse` or `View` — always type-hint return types.

**Services (`app/Services/`):**
- Use for external API integrations and infrastructure concerns (e.g., BybitService, DailyNewsService).
- Keep methods focused — extract private helpers inside the service when needed.
- Inject via constructor with `private readonly`.

**FormRequests (`app/Http/Requests/`):**
- All form validation lives in FormRequest classes — never `$request->validate()` in controllers.
- Use `isMethod('POST')` inside `rules()` to conditionally apply `required` vs `sometimes` for shared store/update requests.
- `authorize()` must return `auth()->check()` — never `false`.
- Custom `messages()` array required — no raw Laravel default messages shown to users.

**No Repository pattern** unless genuinely multi-source data (DB + API + cache fallback).
**No Action classes** — prefer Service layer + private controller helpers for this project.

## DRY PRINCIPLES

- Extract shared query logic into `private` controller methods (e.g., `findOwnedTrade()`).
- Extract shared computation into `private` methods (e.g., `computeDerivedFields()`).
- Extract shared side-effect logic into `private` methods (e.g., `syncReasons()`, `syncLessons()`).
- One `TradeRequest` / `BalanceRequest` handles both store and update via `isMethod()` — no duplicated FormRequest classes.
- Always use `array_filter()` when processing nullable array inputs from forms (`entry_reason[]`, `lesson[]`).
- Use `??` and `isset()` guards before accessing `'sometimes'` validated fields — they may be absent on update.

## SECURITY (strict)

- Never `env('…')` — always `config('key')`.
- All file uploads: validate `mimes`, `max` size, `image` rule. Upload to external service, never store raw in `public/`.
- Policies/Gates for authorization on sensitive routes.
- Parameterized queries / Eloquent only — no raw SQL string concat.
- CSRF on all POST/PUT/DELETE forms.
- `unset()` non-model keys from `$validated` before `->fill()` or `->create()`.

## DATA INTEGRITY (ACID + Idempotency)

**Atomicity — use `DB::transaction()` for multi-table writes:**
- Any operation that writes to more than one table (e.g. trade + reasons + lessons) MUST be wrapped in `DB::transaction()`.
- If any step fails, ALL changes roll back — prevents partial/corrupted state.
- Example: `DB::transaction(fn() => [$trade->save(), $this->syncReasons(...), $this->syncLessons(...)]);`

**External API calls go OUTSIDE the transaction:**
- HTTP calls (e.g. FreeImage.host upload) cannot be rolled back by the DB.
- Always perform external side-effects before opening the transaction.
- If the upload fails, fall back to `null` and let the trade save without it (non-fatal).

**Idempotency — sync pattern for relational array data:**
- For relations submitted as arrays (reasons, lessons): always delete-then-recreate.
- `$trade->reasons()->delete()` then re-insert from the submitted array.
- This means re-submitting the same data twice produces the same final state.
- Never `update()` individual relation rows by index — the array positions may shift.

**`isset()` guards for `'sometimes'` validated fields:**
- Fields with `'sometimes'` rule may be absent from `$validated` on update.
- Always use `isset($validated['field'])` before accessing or computing derived values.
- Accessing absent `'sometimes'` keys directly will throw "Undefined array key".

## BLADE / UI

- **DaisyUI + Tailwind** — use DaisyUI components (`btn`, `input`, `select`, `alert`, `modal`, `fieldset`) over raw Tailwind.
- Dark mode support — use DaisyUI themes, not manual `dark:` classes.
- Form errors: use `@error('field')` inline under each input + top-level `alert alert-error` for error summary.
- Old input: always add `value="{{ old('field', $model->field ?? '') }}"` and `@selected()` on update forms.
- `input-error` class on inputs when `@error` fires.
- `disabled` is invalid on `<option>` placeholder — use `readonly` inputs styled with `bg-gray-200 text-gray-500 cursor-not-allowed` to mimic disabled while still submitting values.
- Pagination links: always render `{{ $collection->links() }}` in index views.

## CODE STYLE

- `declare(strict_types=1);` on every PHP file.
- `final` classes and `readonly` properties where appropriate.
- Typed method parameters and return types always.
- PSR-12 strict formatting.
- Constructor property promotion for injected dependencies.
- Eloquent eager load relations — no N+1 (`->with(['reasons', 'lessons'])`).

## STACK

- Laravel 12 + Vite + Tailwind + DaisyUI.
- Ziggy for typed JS route helpers.
- Blade templates — no Livewire or Inertia unless explicitly requested.
- FreeImage.host for chart image hosting via `Http::asForm()->post()` with `format=json`.

## ALWAYS

- Show all `use` imports in code snippets.
- Use named routes — never hardcoded URLs.
- Add comments for non-obvious logic (especially `'sometimes'` validation behavior).
- Wildcard validation messages (`field.*.rule`) must be explicitly set in `messages()`.
- Suggest running `php artisan optimize`, `./vendor/bin/pint`, and tests after significant changes.

## PRIORITY

**Security > Correctness > DRY > Readability > Performance**