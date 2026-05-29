<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\DispatchesQueueOrSync;
use App\Jobs\AnalyzeTradeJob;
use App\Models\Trade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

final class AiAnalysisController extends Controller
{
    use DispatchesQueueOrSync;

    public function analyze(int $id): RedirectResponse
    {
        $trade = Trade::where('user_id', Auth::id())->findOrFail($id);

        // Set status to pending so UI can show a loader
        $trade->update(['ai_analysis' => 'PENDING']);

        $this->dispatchJob(new AnalyzeTradeJob((int) $id));

        return redirect()->back()->with(
            'success',
            'AI analysis is running. Results will show in the AI Analysis section below shortly.'
        );
    }

    public function destroy(int $id): RedirectResponse
    {
        $trade = Trade::where('user_id', Auth::id())->findOrFail($id);
        $trade->update(['ai_analysis' => null]);

        return redirect()->back()->with('success', 'AI analysis deleted successfully.');
    }
}
