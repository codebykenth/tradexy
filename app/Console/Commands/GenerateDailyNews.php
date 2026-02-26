<?php

namespace App\Console\Commands;

use App\Services\DailyNewsService;
use Illuminate\Console\Command;

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
        $dailyNewsService = new DailyNewsService();
        $news = $dailyNewsService->generate();
        $this->info("Daily news generated successfully.");
        $this->info("Date Range: " . $news['dateRange']);
        $this->info("Gold Articles: {$news['gold']['count']}");
        $this->info("Crypto Articles: {$news['crypto']['count']}");
    }
}
