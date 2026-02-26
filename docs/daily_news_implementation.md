# Daily News Implementation Guide

This document explains the architecture and implementation details for the Daily News extraction and notification system in the **Trading Journal v2** application.

## 1. Required Packages

The core of the news processing engine relies on parsing XML/RSS feeds from different providers.

- **`simplepie/simplepie`**: An RSS and Atom feed parser.
  - **Installation**: Run `composer require simplepie/simplepie` (Already installed in your `composer.json`).
- **Laravel Framework**: Includes default libraries (like `Illuminate\Support\Facades\Http`) which are safely used to fetch current tracking prices (Gold APIs, Binance API).

## 2. Core Components

The Daily News workflow is comprised of four primary components spanning the business logic (Service), the execution trigger (Console Command), and the notification bridge (Mail/View).

### A. The Core Logic (`App\Services\DailyNewsService`)

This is the central processor that manages fetching, mapping, and filtering raw RSS data into usable structured articles.
- **Data Source (`getRssData`)**: Contains hardcoded RSS endpoints categorized for Gold & Commodities (like Nasdaq, Google News, FXStreet) and Crypto (CoinDesk, CoinTelegraph, etc).
- **Execution (`generate`)**: 
  - Iterates over each RSS Feed using `SimplePie`.
  - Sequentially parses the data. Includes strict memory management strategies (`gc_collect_cycles()`, `sleep(1)`) to avoid hitting PHP's memory limit.
  - Fetches the active BTC & Gold prices using `Http::get()`.
- **Filtering System (`filterHighImpactNews`)**: Reconstructs each article and loops over its `title` and `content` scanning against a predefined array of keywords (e.g. `fed`, `inflation`, `recession`). If conditions match, it assigns a `score` and filters out anything that doesn't reach a significant impact threshold over the last 2 days.

### B. The Terminal Command Trigger (`App\Console\Commands\GenerateDailyNews`)

Provides the CLI interface to manually trigger or schedule the fetch operation.
- **Signature**: `php artisan generate:daily-news`
- **Action**: It instantiates (`DailyNewsService`) and invokes the `generate()` method, grabbing the results. Currently, it triggers `$this->info()` to neatly display a summary of the article counts in your terminal without blowing up the memory.
- *Note*: If you decide to send emails here later, you will hook the `Mail::send()` directive into this `handle()` method.

### C. Email Architecture (`App\Mail\DailyNewsMail` & `daily-news-email.blade.php`)

If notifying users via email, the components are already stubbed out:
- **`DailyNewsMail`**: Extends Laravel's `Mailable` class. You can pass the `$news` payload from the command string into this class' constructor so the blade file can interact with it.
- **`daily-news-email.blade.php`**: The visual HTML layout where you will iterate over `$news['gold']['articles']` and `$news['crypto']['articles']` using blade's `@foreach` blocks.

## 3. Automation and Scheduling

To make the process run automatically daily, you utilize Laravel’s Task Scheduler registered within `routes/console.php`.

```php
if (app()->environment('production')) {
    // Other commands...
    Schedule::command('generate:daily-news')->dailyAt('08:00');
}
```
- **How it Works**: In production, the system cron will invoke `php artisan schedule:run` every minute. When `08:00` hits, it dynamically fires off the `generate:daily-news` command under the hood.

## 4. How to Extend or Modify

1. **Adding New Data Sources**: Open `DailyNewsService.php` and add your new XML feed into the `$this->getRssData()` array.
2. **Editing Keywords/Scoring Weight**: Modify the `GOLD_KEYWORDS` or `CRYPTO_KEYWORDS` constants at the top of `DailyNewsService.php`. The higher the assigned integer, the higher priority these articles get when sorting the results.
3. **Triggering Emails**: In `GenerateDailyNews.php`, import the Mail facade and trigger an email loop.
    ```php
    use Illuminate\Support\Facades\Mail;
    use App\Mail\DailyNewsMail;
    
    // ... inside handle()
    Mail::to('user@example.com')->send(new DailyNewsMail($news));
    ```

## 5. Troubleshooting (Memory Leaks)
RSS feed polling can bloat memory easily since SimplePie hoards document structures.
If you ever encounter `Allowed memory size exhausted` errors:
- Ensure you process datasets *in smaller chunks* and merge immediately.
- Always run `$item->__destruct()` individually after extraction.
- Force Laravel to garbage collect immediately with `unset()` and `gc_collect_cycles()`.
