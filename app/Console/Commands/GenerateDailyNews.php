<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\DailyNewsMail;
use App\Models\ActivityLog;
use App\Services\DailyNewsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class GenerateDailyNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:daily-news';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $dailyNewsService = new DailyNewsService;
            $news = $dailyNewsService->generate();
            $this->info('Daily news generated successfully.');
            $this->info('Date Range: '.$news['dateRange']);
            $this->info("Gold Articles: {$news['gold']['count']}");
            $this->info("Crypto Articles: {$news['crypto']['count']}");

            // Persist to database for the AI News View
            \App\Models\MarketNews::create([
                'date_range' => $news['dateRange'],
                'ai_analysis' => $news['aiAnalysis'],
            ]);

            // Broadcast real-time update
            event(new \App\Events\MarketNewsGenerated);

            \App\Models\ActivityLog::create([
                'action' => 'daily_news_generated',
                'description' => "Market insights generated for {$news['dateRange']}. Gold: {$news['gold']['count']}, Crypto: {$news['crypto']['count']}",
            ]);

            $email = config('services.bybit.user_email');
            if ($email) {
                Mail::to($email)->send(new DailyNewsMail($news['aiAnalysis']));
                $this->info("Daily news mail sent to {$email}.");
            } else {
                $this->warn('No Bybit user email configured. Could not send the mail.');
            }
        } catch (\Exception $e) {
            $this->error("Daily news generation failed: {$e->getMessage()}");

            $email = config('services.bybit.user_email');
            if ($email) {
                Mail::to($email)->send(
                    new \App\Mail\Errors\GenericJobFailedMail('Market News Generation', $e->getMessage())
                );
            }

            ActivityLog::create([
                'action' => 'daily_news_failed',
                'description' => 'Market news generation failed: '.substr($e->getMessage(), 0, 200),
            ]);

            return 1;
        }
    }
}
