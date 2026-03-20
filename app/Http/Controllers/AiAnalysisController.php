<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\AnalyzeTradeJob;
use App\Models\Trade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

final class AiAnalysisController extends Controller
{
    public function analyze(int $id): RedirectResponse
    {
        $trade = Trade::where('user_id', Auth::id())->findOrFail($id);

        // Set status to pending so UI can show a loader
        $trade->update(['ai_analysis' => 'PENDING']);

        AnalyzeTradeJob::dispatch((int) $id);

        return redirect()->back()->with('success', 'AI is currently analyzing your chart. It will appear here shortly!');
    }

    public function destroy(int $id): RedirectResponse
    {
        $trade = Trade::where('user_id', Auth::id())->findOrFail($id);
        $trade->update(['ai_analysis' => null]);

        return redirect()->back()->with('success', 'AI analysis deleted successfully.');
    }
}
