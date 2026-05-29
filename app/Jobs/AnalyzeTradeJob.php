<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\TradeAnalysisGenerated;
use App\Models\Trade;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

final class AnalyzeTradeJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly int $tradeId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $trade = Trade::with(['strategy', 'lessons', 'reasons'])->findOrFail($this->tradeId);

        $lessons = $trade->lessons->pluck('lesson')->implode(', ');
        $entryReasons = $trade->reasons->where('type', 'entry')->pluck('reason')->implode(', ');
        $exitReasons = $trade->reasons->where('type', 'exit')->pluck('reason')->implode(', ');

        $imageContent = @file_get_contents((string) $trade->direct_chart_url);
        if ($imageContent === false) {
            throw new Exception('Failed to download chart image.');
        }
        $base64Image = base64_encode($imageContent);

        $mimeType = 'image/png';
        $url = strtolower((string) $trade->direct_chart_url);
        if (str_ends_with($url, '.jpg') || str_ends_with($url, '.jpeg')) {
            $mimeType = 'image/jpeg';
        } elseif (str_ends_with($url, '.webp')) {
            $mimeType = 'image/webp';
        }

        $strategy = $trade->strategy->name ?? 'N/A';

        $systemPrompt = 'You are a world-class, no-BS trading mentor and forensic chart auditor with 20+ years crushing institutional desks. Your mission is to deliver a brutally honest, evidence-based post-mortem on this exact trade so I evolve into a consistently profitable trader — no excuses, no generic platitudes.';
        $userPrompt = "Analyze this chart for a {$trade->entry_side} position on {$trade->symbol}.\n\n".
            "### Trade Details:\n".
            "- **Strategy:** {$strategy}\n".
            "- **Timeframe:** {$trade->timeframe}\n".
            "- **Entry:** {$trade->avg_entry_price} (Reason: {$entryReasons})\n".
            "- **Exit:** {$trade->avg_exit_price} (Reason: {$exitReasons})\n".
            "- **PnL:** {$trade->total_pnl}\n".
            "- **Target/Stop:** TP: {$trade->take_profit_price}, SL: {$trade->stop_loss_price}\n".
            "- **Risk/Reward Ratio:** {$trade->risk_reward}R\n".
            "- **Emotions:** Entry: {$trade->entry_emotion}, Exit: {$trade->exit_emotion}\n".
            "- **User's Self-Reflection/Lessons:** {$lessons}\n\n".
            "Based on the visual evidence in the chart and the trade data above, provide a ruthless but constructive critique. **Specifically evaluate my stated reasons for entering/exiting and my self-reported lessons—are they accurate to what the chart shows, or am I lying to myself?**\n\n".
            "Please output your response in the following Markdown format:\n\n".
            "### 📊 Market Context & Structure\n".
            "- Higher-timeframe bias (if inferable)\n".
            "- Dominant trend & market structure at entry/exit\n".
            "- Key levels (S/R, liquidity pools, etc.) respected or violated\n\n".
            "### 🎯 Intended Setup & Edge Validity\n".
            "- **Edge Alignment:** Did this trade match a high-probability edge? (Explain the specific edge found or missing)\n".
            "- Critique of my Entry/Exit Reasons (Valid edge or FOMO/luck?)\n".
            "- Critique of my Self-Reported Lessons (Are they the *real* lessons needed?)\n".
            "- Actual price action outcome & R:R achieved\n\n".
            "### ⚖️ Risk & Trade Management Analysis\n".
            "- **Stop Loss Placement:** Critique my SL placement. Was it safe, too tight, or technically invalid? Where *should* it have been?\n".
            "- **Risk/Reward & Management:** Evaluation of the Risk/Reward ratio and how the trade was managed (or mismanaged).\n\n".
            "### ✅ What You Did Well (Reinforce)\n".
            "- 4–7 bullet points of strong execution (precise timing, discipline, level respect, etc.)\n\n".
            "### ⚠️ Critical Mistakes & Psychology Check\n".
            "- **Technical Error:** (e.g., entered too early)\n".
            "- **Psychological Root:** (e.g., FOMO because you missed the previous move)\n".
            "- **Evidence:** Quote chart or journal emotion that proves this.\n\n".
            "### 🛠 How to Prevent These Mistakes Forever\n".
            "- For each major issue above, give 1–2 concrete, enforceable rules/checklist items/habits.\n\n".
            "### 🚀 One Thing to Nail Next Trade\n".
            "- The single highest-leverage change or focus area that would have turned this trade around or prevented the loss (or maximized the win).\n\n".
            "### Trade Execution Score\n".
            "- Grade: A+ to F (justify with 2–3 sentences)\n".
            '- Execution quality 1–10 (how closely it matched high-probability process)';

        $response = Http::withHeaders([
            'x-goog-api-key' => config('services.gemini.key'),
            'Content-Type' => 'application/json',
        ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent', [
            'system_instruction' => ['parts' => [['text' => $systemPrompt]]],
            'contents' => [
                [
                    'parts' => [
                        ['text' => $userPrompt],
                        ['inline_data' => ['mime_type' => $mimeType, 'data' => $base64Image]],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 1.0,
                'topP' => 0.8,
                'topK' => 10,
                'thinkingConfig' => ['thinkingLevel' => 'medium'],
            ],
        ]);

        if (!$response->successful()) {
            throw new Exception('Gemini API request failed: '.$response->body());
        }

        $responseData = $response->json();
        $analysis = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'No analysis generated.';

        $trade->update(['ai_analysis' => $analysis]);

        // Broadcast the result when REALTIME_ENABLED=true (Pusher)
        if (config('app.realtime_enabled')) {
            broadcast(new TradeAnalysisGenerated($trade));
        }
    }
}
