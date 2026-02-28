<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TradeRequest;
use App\Models\Trade;
use App\Services\BybitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

final class TradeController extends Controller
{
    public function __construct(private readonly BybitService $bybitService)
    {
    }

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

        $trade = new Trade([
            'user_id' => Auth::id(),
            'order_id' => Str::random(14),
        ]);

        return $this->persistTrade($trade, $validated, $request);
    }

    public function update(TradeRequest $request, int $id): RedirectResponse
    {
        $validated = $request->validated();
        $trade = $this->findOwnedTrade($id);

        return $this->persistTrade($trade, $validated, $request);
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->findOwnedTrade($id)->delete();

        return redirect()->route('trades.index')
            ->with('success', 'Trade deleted successfully.');
    }

    // Finds authenticated user's trade, throws 404 if not found
    private function findOwnedTrade(int $id, array $with = []): Trade
    {
        return Trade::where('user_id', Auth::id())
            ->with($with)
            ->findOrFail($id);
    }

    // Shared persistence logic for store() and update() with atomic transaction
    private function persistTrade(Trade $trade, array $validated, TradeRequest $request): RedirectResponse
    {
        $validated = $this->computeDerivedFields($validated);

        // Upload chart before transaction — external API call is not transactional
        $validated['chart_picture'] = $this->uploadChartImage($request, $validated['chart_picture'] ?? null);

        $entryReasons = array_filter($validated['entry_reason'] ?? []);
        $exitReasons = array_filter($validated['exit_reason'] ?? []);
        $lessons = array_filter($validated['lesson'] ?? []);
        unset($validated['entry_reason'], $validated['exit_reason'], $validated['lesson']);

        $trade->fill($validated)->save();
        $this->syncReasons($trade, $entryReasons, $exitReasons);
        $this->syncLessons($trade, $lessons);

        $action = $trade->wasRecentlyCreated ? 'created' : 'updated';

        return redirect()->route('trades.index')
            ->with('success', "Trade {$action} successfully.");
    }

    // Recalculates server-side derived fields (symbol, entry/exit totals)
    private function computeDerivedFields(array $validated): array
    {
        if (isset($validated['symbol'])) {
            $validated['symbol'] = strtoupper($validated['symbol']);
        }

        if (isset($validated['avg_entry_price'], $validated['quantity'])) {
            $validated['cum_entry_value'] ??= $validated['avg_entry_price'] * $validated['quantity'];
        }

        if (!empty($validated['avg_exit_price']) && isset($validated['quantity'])) {
            $validated['cum_exit_value'] ??= $validated['avg_exit_price'] * $validated['quantity'];
        }

        return $validated;
    }

    // Uploads chart image to FreeImage.host, returns URL or null on failure
    private function uploadChartImage(TradeRequest $request, mixed $existingValue): ?string
    {
        if (!$request->hasFile('chart_picture')) {
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
            report($e);
            return null;
        }
    }

    // Deletes then recreates entry/exit reasons (idempotent sync)
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

    // Deletes then recreates lessons (idempotent sync)
    private function syncLessons(Trade $trade, array $lessons): void
    {
        $trade->lessons()->delete();

        foreach ($lessons as $lesson) {
            $trade->lessons()->create(['lesson' => $lesson, 'category' => 'N/A']);
        }
    }
}
