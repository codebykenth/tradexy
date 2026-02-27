<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TradeRequest;
use App\Models\Trade;
use App\Services\BybitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

final class TradeController extends Controller
{
    public function __construct(private readonly BybitService $bybitService)
    {
    }

    // =========================================================================
    // RESOURCE METHODS
    // =========================================================================

    public function index()
    {
        $ownedTrades = Trade::where('user_id', Auth::id())
            ->latest('close_datetime')
            ->paginate(10);

        return view('trades.index', compact('ownedTrades'));
    }

    public function create()
    {
        return view('trades.create');
    }

    public function show(int $id)
    {
        $trade = $this->findOwnedTrade($id, ['strategy', 'lessons', 'reasons']);

        return view('trades.show', compact('trade'));
    }

    public function edit(int $id)
    {
        $trade = $this->findOwnedTrade($id, ['strategy', 'lessons', 'reasons']);

        return view('trades.edit', compact('trade'));
    }

    public function store(TradeRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // New trade — assign owner and generate a unique order ID
        $trade = new Trade([
            'user_id' => Auth::id(),
            'order_id' => Str::random(14),
        ]);

        return $this->persistTrade($trade, $validated, $request);
    }

    public function update(TradeRequest $request, int $id): RedirectResponse
    {
        $validated = $request->validated();

        // Existing trade — ownership is verified in findOwnedTrade()
        $trade = $this->findOwnedTrade($id);

        return $this->persistTrade($trade, $validated, $request);
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->findOwnedTrade($id)->delete();

        return redirect()->route('trades.index')
            ->with('success', 'Trade deleted successfully.');
    }

    // =========================================================================
    // PRIVATE HELPERS — shared logic between store() and update()
    // =========================================================================

    /**
     * Find a trade that belongs to the authenticated user.
     * Throws 404 automatically if not found or doesn't belong to the user.
     *
     * @param  array<string>  $with  Eager-load relations
     */
    private function findOwnedTrade(int $id, array $with = []): Trade
    {
        return Trade::where('user_id', Auth::id())
            ->with($with)
            ->findOrFail($id);
    }

    /**
     * Core persistence logic shared by store() and update().
     *
     * ACID — Atomicity:
     *   The trade row + reasons + lessons are written inside a single DB transaction.
     *   If any step fails, ALL changes roll back — no partial/corrupted state.
     *   e.g. if syncReasons() throws after fill()->save(), the trade row is rolled back too.
     *
     * Idempotency (update):
     *   syncReasons() and syncLessons() always delete-then-recreate, so calling
     *   the same update twice with identical data produces the same final state.
     *
     * Image upload is intentionally OUTSIDE the transaction because:
     *   - It is an external HTTP call (not part of PostgreSQL's ACID scope).
     *   - Rolling back a DB transaction cannot undo an already-uploaded image.
     *   - If it fails, we fall back to null (non-fatal) and the trade still saves.
     *
     * @param array<string, mixed> $validated
     */
    private function persistTrade(Trade $trade, array $validated, TradeRequest $request): RedirectResponse
    {
        // 1. Compute server-side derived fields (symbol uppercase, entry total, exit total)
        $validated = $this->computeDerivedFields($validated);

        // 2. Upload chart BEFORE the transaction — external API call is not transactional
        $validated['chart_picture'] = $this->uploadChartImage($request, $validated['chart_picture'] ?? null);

        // 3. Extract relational data — these are not Trade model columns
        $entryReasons = array_filter($validated['entry_reason'] ?? []);
        $exitReasons = array_filter($validated['exit_reason'] ?? []);
        $lessons = array_filter($validated['lesson'] ?? []);
        unset($validated['entry_reason'], $validated['exit_reason'], $validated['lesson']);

        try {
            // 4. Atomic transaction: trade + reasons + lessons all commit or all roll back
            DB::transaction(function () use ($trade, $validated, $entryReasons, $exitReasons, $lessons): void {
                // fill()->save() runs INSERT for new models, UPDATE for existing ones
                $trade->fill($validated)->save();

                // Sync is idempotent: delete-then-recreate gives the same result on retry
                $this->syncReasons($trade, $entryReasons, $exitReasons);
                $this->syncLessons($trade, $lessons);
            });

        } catch (\Exception $e) {
            report($e);
            return back()
                ->withInput()
                ->withErrors(['general' => 'Something went wrong while saving the trade. Please try again.']);
        }

        $action = $trade->wasRecentlyCreated ? 'created' : 'updated';

        return redirect()->route('trades.index')
            ->with('success', "Trade {$action} successfully.");
    }

    /**
     * Compute fields that are derived from other user inputs.
     * These are readonly in the UI (auto-calculated by JS), so we recalculate
     * server-side for integrity.
     *
     * @param  array<string, mixed> $validated
     * @return array<string, mixed>
     */
    private function computeDerivedFields(array $validated): array
    {
        // Only uppercase if symbol is present (it's 'sometimes' on update)
        if (isset($validated['symbol'])) {
            $validated['symbol'] = strtoupper($validated['symbol']);
        }

        // Only compute entry total if both price and quantity were submitted
        if (isset($validated['avg_entry_price'], $validated['quantity'])) {
            $validated['cum_entry_value'] ??= $validated['avg_entry_price'] * $validated['quantity'];
        }

        // Only compute exit total if exit price and quantity were submitted
        if (!empty($validated['avg_exit_price']) && isset($validated['quantity'])) {
            $validated['cum_exit_value'] ??= $validated['avg_exit_price'] * $validated['quantity'];
        }

        return $validated;
    }

    /**
     * Upload chart image to FreeImage.host and return the hosted URL.
     * Returns null (and logs the error) if upload fails — non-fatal.
     * Returns the existing URL untouched if no new file was uploaded.
     */
    private function uploadChartImage(TradeRequest $request, mixed $existingValue): ?string
    {
        if (!$request->hasFile('chart_picture')) {
            // No new file uploaded — keep existing URL (string) or null
            return is_string($existingValue) ? $existingValue : null;
        }

        try {
            $base64 = base64_encode(
                file_get_contents($request->file('chart_picture')->path())
            );

            $response = Http::asForm()->post('https://freeimage.host/api/1/upload', [
                'key' => config('services.freeimg.key'),
                'source' => $base64,
                'format' => 'json',
            ]);

            return $response->json('image.display_url');
        } catch (\Exception $e) {
            report($e); // Logs silently, trade still saves without chart
            return null;
        }
    }

    /**
     * Sync entry and exit reasons for a trade.
     * Deletes all existing reasons and recreates from submitted data.
     * This is the simplest correct approach for array form inputs.
     *
     * @param  array<int, string>  $entryReasons
     * @param  array<int, string>  $exitReasons
     */
    private function syncReasons(Trade $trade, array $entryReasons, array $exitReasons): void
    {
        $trade->reasons()->delete();

        foreach ($entryReasons as $reason) {
            $trade->reasons()->create(['type' => 'entry', 'reason' => $reason]);
        }

        foreach ($exitReasons as $reason) {
            $trade->reasons()->create(['type' => 'exit', 'reason' => $reason]);
        }
    }

    /**
     * Sync lessons for a trade.
     * Deletes all existing lessons and recreates from submitted data.
     *
     * @param  array<int, string>  $lessons
     */
    private function syncLessons(Trade $trade, array $lessons): void
    {
        $trade->lessons()->delete();

        foreach ($lessons as $lesson) {
            $trade->lessons()->create(['lesson' => $lesson, 'category' => 'N/A']);
        }
    }

    public function testTrades(): mixed
    {
        $response = $this->bybitService->getClosedPnl(Auth::id());

        if (!empty($response['result']['list'])) {
            return $response['result']['list'][0];
        }

        return response()->json(['message' => 'No closed pnl found for this period.']);
    }
}
