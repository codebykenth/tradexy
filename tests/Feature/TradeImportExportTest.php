<?php

declare(strict_types=1);

use App\Models\Strategy;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

test('authenticated user can download the import CSV template', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create(['terms_accepted_at' => now()]);

    $response = $this->actingAs($user)->get(route('trades.template'));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $response->assertHeader('content-disposition', 'attachment; filename="tradexy_import_template.csv"');
});

test('authenticated user can export trades as CSV', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create(['terms_accepted_at' => now()]);

    Trade::create([
        'user_id' => $user->id,
        'order_id' => 'ORDTEST1234567',
        'market' => 'crypto',
        'symbol' => 'ETHUSDT',
        'entry_side' => 'long',
        'exit_side' => 'short',
        'quantity' => 1.5,
        'cum_entry_value' => 3000,
        'cum_exit_value' => 3300,
        'avg_entry_price' => 2000,
        'avg_exit_price' => 2200,
        'leverage' => 1,
        'open_fees' => 1.5,
        'close_fees' => 1.5,
        'closed_pnl' => 300,
        'total_pnl' => 297,
        'chart_picture' => 'https://storage.googleapis.com/test-bucket/chart.png',
        'ai_analysis' => 'Bullish continuation setup.',
        'open_datetime' => now()->subDay(),
        'close_datetime' => now(),
        'is_demo' => false,
    ]);

    $response = $this->actingAs($user)->get(route('trades.export'));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    expect($response->headers->get('content-disposition'))->toContain('trades_export_');
});

test('exporting trades with date filter only includes filtered trades in CSV', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create(['terms_accepted_at' => now()]);

    // Feb trade
    Trade::create([
        'user_id' => $user->id,
        'order_id' => 'ORD_FEB_TRADE',
        'market' => 'crypto',
        'symbol' => 'SOLUSDT',
        'entry_side' => 'long',
        'exit_side' => 'short',
        'quantity' => 10,
        'cum_entry_value' => 1000,
        'cum_exit_value' => 1200,
        'avg_entry_price' => 100,
        'avg_exit_price' => 120,
        'leverage' => 1,
        'closed_pnl' => 200,
        'total_pnl' => 200,
        'open_datetime' => '2026-02-10 10:00:00',
        'close_datetime' => '2026-02-10 12:00:00',
        'is_demo' => false,
    ]);

    // March trade
    Trade::create([
        'user_id' => $user->id,
        'order_id' => 'ORD_MAR_TRADE',
        'market' => 'crypto',
        'symbol' => 'AVAXUSDT',
        'entry_side' => 'long',
        'exit_side' => 'short',
        'quantity' => 50,
        'cum_entry_value' => 1500,
        'cum_exit_value' => 1800,
        'avg_entry_price' => 30,
        'avg_exit_price' => 36,
        'leverage' => 1,
        'closed_pnl' => 300,
        'total_pnl' => 300,
        'open_datetime' => '2026-03-15 10:00:00',
        'close_datetime' => '2026-03-15 12:00:00',
        'is_demo' => false,
    ]);

    $response = $this->actingAs($user)->get(route('trades.export', [
        'start_date' => '2026-03-01',
        'end_date' => '2026-03-31',
    ]));

    $response->assertStatus(200);
    $csvOutput = $response->streamedContent();

    expect($csvOutput)->toContain('AVAXUSDT');
    expect($csvOutput)->not->toContain('SOLUSDT');
});

test('authenticated user can filter trades by date range', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create(['terms_accepted_at' => now()]);

    // Trade 1: Feb 15, 2026
    Trade::create([
        'user_id' => $user->id,
        'order_id' => 'ORDDATE1',
        'market' => 'crypto',
        'symbol' => 'BTCUSDT',
        'entry_side' => 'long',
        'exit_side' => 'short',
        'quantity' => 1,
        'cum_entry_value' => 60000,
        'cum_exit_value' => 61000,
        'avg_entry_price' => 60000,
        'avg_exit_price' => 61000,
        'leverage' => 1,
        'closed_pnl' => 1000,
        'total_pnl' => 1000,
        'open_datetime' => '2026-02-15 10:00:00',
        'close_datetime' => '2026-02-15 14:00:00',
        'is_demo' => false,
    ]);

    // Trade 2: March 5, 2026
    Trade::create([
        'user_id' => $user->id,
        'order_id' => 'ORDDATE2',
        'market' => 'crypto',
        'symbol' => 'ETHUSDT',
        'entry_side' => 'long',
        'exit_side' => 'short',
        'quantity' => 2,
        'cum_entry_value' => 4000,
        'cum_exit_value' => 4400,
        'avg_entry_price' => 2000,
        'avg_exit_price' => 2200,
        'leverage' => 1,
        'closed_pnl' => 400,
        'total_pnl' => 400,
        'open_datetime' => '2026-03-05 10:00:00',
        'close_datetime' => '2026-03-05 14:00:00',
        'is_demo' => false,
    ]);

    // Filter for March trades only
    $response = $this->actingAs($user)->get(route('trades.index', [
        'start_date' => '2026-03-01',
        'end_date' => '2026-03-31',
    ]));

    $response->assertStatus(200);
    $trades = $response->viewData('ownedTrades');
    expect($trades->total())->toBe(1);
    expect($trades->first()->order_id)->toBe('ORDDATE2');
});

test('authenticated user can filter trades by symbol, outcome, and side', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create(['terms_accepted_at' => now()]);

    // Trade 1: Long BTC Win
    Trade::create([
        'user_id' => $user->id,
        'order_id' => 'ORD_BTC_WIN',
        'market' => 'crypto',
        'symbol' => 'BTCUSDT',
        'entry_side' => 'long',
        'exit_side' => 'short',
        'quantity' => 1,
        'cum_entry_value' => 50000,
        'cum_exit_value' => 52000,
        'avg_entry_price' => 50000,
        'avg_exit_price' => 52000,
        'leverage' => 1,
        'closed_pnl' => 2000,
        'total_pnl' => 2000,
        'open_datetime' => now()->subDays(2),
        'close_datetime' => now()->subDay(),
        'is_demo' => false,
    ]);

    // Trade 2: Short ETH Loss
    Trade::create([
        'user_id' => $user->id,
        'order_id' => 'ORD_ETH_LOSS',
        'market' => 'crypto',
        'symbol' => 'ETHUSDT',
        'entry_side' => 'short',
        'exit_side' => 'long',
        'quantity' => 2,
        'cum_entry_value' => 6000,
        'cum_exit_value' => 6500,
        'avg_entry_price' => 3000,
        'avg_exit_price' => 3250,
        'leverage' => 1,
        'closed_pnl' => -500,
        'total_pnl' => -500,
        'open_datetime' => now()->subDays(2),
        'close_datetime' => now()->subDay(),
        'is_demo' => false,
    ]);

    // Test Symbol filter
    $respSymbol = $this->actingAs($user)->get(route('trades.index', ['symbol' => 'BTC']));
    $respSymbol->assertStatus(200);
    $trades = $respSymbol->viewData('ownedTrades');
    expect($trades->total())->toBe(1);
    expect($trades->first()->order_id)->toBe('ORD_BTC_WIN');

    // Test Outcome filter (losses)
    $respOutcome = $this->actingAs($user)->get(route('trades.index', ['outcome' => 'loss']));
    $respOutcome->assertStatus(200);
    $tradesLoss = $respOutcome->viewData('ownedTrades');
    expect($tradesLoss->total())->toBe(1);
    expect($tradesLoss->first()->order_id)->toBe('ORD_ETH_LOSS');

    // Test Side filter (short)
    $respSide = $this->actingAs($user)->get(route('trades.index', ['side' => 'short']));
    $respSide->assertStatus(200);
    $tradesShort = $respSide->viewData('ownedTrades');
    expect($tradesShort->total())->toBe(1);
    expect($tradesShort->first()->order_id)->toBe('ORD_ETH_LOSS');
});

test('authenticated user can import valid trades from CSV', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create(['terms_accepted_at' => now()]);
    $strategy = Strategy::create([
        'user_id' => $user->id,
        'name' => 'Breakout Master',
    ]);

    $csvContent = "order_id,market,symbol,entry_side,exit_side,quantity,avg_entry_price,avg_exit_price,leverage,open_datetime,close_datetime,timeframe,strategy,chart_picture,ai_analysis,entry_reasons,exit_reasons,lessons,is_demo\n"
        ."ORDIMP001,crypto,SOLUSDT,long,short,10,150.00,165.00,2,2026-03-01 10:00:00,2026-03-01 14:00:00,1hr,Breakout Master,https://storage.googleapis.com/test-bucket/chart.png,Bullish bounce analysis,Support bounce; Volume breakout,Resistance reached,Patience was key,0\n";

    $file = UploadedFile::fake()->createWithContent('trades.csv', $csvContent);

    $response = $this->actingAs($user)->post(route('trades.import'), [
        'file' => $file,
    ]);

    $response->assertRedirect(route('trades.index'));
    $response->assertSessionHas('success');

    $trade = Trade::where('user_id', $user->id)->where('symbol', 'SOLUSDT')->first();
    expect($trade)->not->toBeNull();
    expect($trade->order_id)->toBe('ORDIMP001');
    expect($trade->strategy_id)->toBe($strategy->id);
    expect((float) $trade->quantity)->toBe(10.0);
    expect((float) $trade->avg_entry_price)->toBe(150.0);
    expect((float) $trade->avg_exit_price)->toBe(165.0);
    expect((float) $trade->total_pnl)->toBe(150.0); // (165-150)*10 = 150 gross - 0 fees
    expect($trade->chart_picture)->toBe('https://storage.googleapis.com/test-bucket/chart.png');
    expect($trade->ai_analysis)->toBe('Bullish bounce analysis');

    // Verify relations
    expect($trade->reasons()->where('type', 'entry')->count())->toBe(2);
    expect($trade->reasons()->where('type', 'exit')->count())->toBe(1);
    expect($trade->lessons()->count())->toBe(1);
});

test('import skips duplicate order_ids gracefully', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create(['terms_accepted_at' => now()]);

    Trade::create([
        'user_id' => $user->id,
        'order_id' => 'ORDDUPLICATE1',
        'market' => 'crypto',
        'symbol' => 'BTCUSDT',
        'entry_side' => 'long',
        'exit_side' => 'short',
        'quantity' => 1,
        'cum_entry_value' => 60000,
        'cum_exit_value' => 62000,
        'avg_entry_price' => 60000,
        'avg_exit_price' => 62000,
        'leverage' => 1,
        'closed_pnl' => 2000,
        'total_pnl' => 2000,
        'open_datetime' => now()->subDay(),
        'close_datetime' => now(),
        'is_demo' => false,
    ]);

    $csvContent = "order_id,market,symbol,entry_side,exit_side,quantity,avg_entry_price,avg_exit_price,open_datetime\n"
        ."ORDDUPLICATE1,crypto,BTCUSDT,long,short,1,60000,62000,2026-03-01 10:00:00\n"
        ."ORDNEWTRADE2,crypto,ADAUSDT,long,short,100,0.50,0.60,2026-03-01 10:00:00\n";

    $file = UploadedFile::fake()->createWithContent('trades.csv', $csvContent);

    $response = $this->actingAs($user)->post(route('trades.import'), [
        'file' => $file,
    ]);

    $response->assertRedirect(route('trades.index'));
    $response->assertSessionHas('success');

    expect(Trade::where('user_id', $user->id)->count())->toBe(2);
    expect(Trade::where('order_id', 'ORDNEWTRADE2')->exists())->toBeTrue();
});

test('import keeps only 1 copy when multiple duplicate rows exist in the same CSV file', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create(['terms_accepted_at' => now()]);

    // CSV containing 3 duplicate rows without order_id and 1 distinct row
    $csvContent = "market,symbol,entry_side,exit_side,quantity,avg_entry_price,avg_exit_price,open_datetime\n"
        ."crypto,ETHUSDT,long,short,2.5,3000.00,3200.00,2026-03-01 12:00:00\n"
        ."crypto,ETHUSDT,long,short,2.5,3000.00,3200.00,2026-03-01 12:00:00\n"
        ."crypto,ETHUSDT,long,short,2.5,3000.00,3200.00,2026-03-01 12:00:00\n"
        ."crypto,BNBUSDT,long,short,10,500.00,550.00,2026-03-01 12:00:00\n";

    $file = UploadedFile::fake()->createWithContent('trades_with_duplicates.csv', $csvContent);

    $response = $this->actingAs($user)->post(route('trades.import'), [
        'file' => $file,
    ]);

    $response->assertRedirect(route('trades.index'));
    $response->assertSessionHas('success');

    // Exactly 2 trades should exist in DB (1 ETHUSDT and 1 BNBUSDT), 2 duplicate ETH rows were skipped
    expect(Trade::where('user_id', $user->id)->count())->toBe(2);
    expect(Trade::where('user_id', $user->id)->where('symbol', 'ETHUSDT')->count())->toBe(1);
    expect(Trade::where('user_id', $user->id)->where('symbol', 'BNBUSDT')->count())->toBe(1);
});

test('import fails with error when required columns are missing', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create(['terms_accepted_at' => now()]);

    $csvContent = "random_column_1,random_column_2\n123,456\n";
    $file = UploadedFile::fake()->createWithContent('invalid.csv', $csvContent);

    $response = $this->actingAs($user)->post(route('trades.import'), [
        'file' => $file,
    ]);

    $response->assertSessionHas('error');
    expect(Trade::where('user_id', $user->id)->count())->toBe(0);
});
