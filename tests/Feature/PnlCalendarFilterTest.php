<?php

declare(strict_types=1);

use App\Models\Strategy;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can view pnl calendar', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create(['terms_accepted_at' => now()]);

    Trade::create([
        'user_id' => $user->id,
        'order_id' => 'ORD_CAL_1',
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
        'open_datetime' => now()->startOfMonth(),
        'close_datetime' => now()->startOfMonth()->addHours(2),
        'is_demo' => false,
    ]);

    $response = $this->actingAs($user)->get(route('pnl-calendar.index'));

    $response->assertStatus(200);
    $response->assertSee('PnL Calendar');
    $response->assertSee('+$2,000.00');
    $response->assertSee('Win Days');
});

test('pnl calendar can be filtered by strategy', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create(['terms_accepted_at' => now()]);

    $stratA = Strategy::create(['user_id' => $user->id, 'name' => 'Breakout Edge']);
    $stratB = Strategy::create(['user_id' => $user->id, 'name' => 'Mean Reversion']);

    // Trade with Strat A (Win +500)
    Trade::create([
        'user_id' => $user->id,
        'strategy_id' => $stratA->id,
        'order_id' => 'ORD_STRAT_A',
        'market' => 'crypto',
        'symbol' => 'BTCUSDT',
        'entry_side' => 'long',
        'exit_side' => 'short',
        'quantity' => 1,
        'cum_entry_value' => 60000,
        'cum_exit_value' => 60500,
        'avg_entry_price' => 60000,
        'avg_exit_price' => 60500,
        'leverage' => 1,
        'closed_pnl' => 500,
        'total_pnl' => 500,
        'open_datetime' => now()->startOfMonth(),
        'close_datetime' => now()->startOfMonth()->addHours(2),
        'is_demo' => false,
    ]);

    // Trade with Strat B (Loss -300)
    Trade::create([
        'user_id' => $user->id,
        'strategy_id' => $stratB->id,
        'order_id' => 'ORD_STRAT_B',
        'market' => 'crypto',
        'symbol' => 'ETHUSDT',
        'entry_side' => 'short',
        'exit_side' => 'long',
        'quantity' => 1,
        'cum_entry_value' => 3000,
        'cum_exit_value' => 3300,
        'avg_entry_price' => 3000,
        'avg_exit_price' => 3300,
        'leverage' => 1,
        'closed_pnl' => -300,
        'total_pnl' => -300,
        'open_datetime' => now()->startOfMonth(),
        'close_datetime' => now()->startOfMonth()->addHours(3),
        'is_demo' => false,
    ]);

    // Filter by Strategy A -> Total PnL should be +$500.00
    $response = $this->actingAs($user)->get(route('pnl-calendar.index', [
        'strategy_id' => $stratA->id,
        'year' => now()->year,
        'month' => now()->month,
    ]));

    $response->assertStatus(200);
    $response->assertSee('+$500.00');
    $response->assertSee('Strategy: Breakout Edge');

    // Filter by Strategy B -> Total PnL should be -$300.00
    $responseB = $this->actingAs($user)->get(route('pnl-calendar.index', [
        'strategy_id' => $stratB->id,
        'year' => now()->year,
        'month' => now()->month,
    ]));

    $responseB->assertStatus(200);
    $responseB->assertSee('-$300.00');
    $responseB->assertSee('Strategy: Mean Reversion');
});

test('pnl calendar can be filtered by symbol and side', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create(['terms_accepted_at' => now()]);

    Trade::create([
        'user_id' => $user->id,
        'order_id' => 'ORD_BTC_LONG',
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
        'open_datetime' => now()->startOfMonth(),
        'close_datetime' => now()->startOfMonth()->addHours(1),
        'is_demo' => false,
    ]);

    Trade::create([
        'user_id' => $user->id,
        'order_id' => 'ORD_SOL_SHORT',
        'market' => 'crypto',
        'symbol' => 'SOLUSDT',
        'entry_side' => 'short',
        'exit_side' => 'long',
        'quantity' => 10,
        'cum_entry_value' => 1500,
        'cum_exit_value' => 1600,
        'avg_entry_price' => 150,
        'avg_exit_price' => 160,
        'leverage' => 1,
        'closed_pnl' => -100,
        'total_pnl' => -100,
        'open_datetime' => now()->startOfMonth(),
        'close_datetime' => now()->startOfMonth()->addHours(2),
        'is_demo' => false,
    ]);

    // Filter by symbol SOL
    $response = $this->actingAs($user)->get(route('pnl-calendar.index', [
        'symbol' => 'SOL',
        'year' => now()->year,
        'month' => now()->month,
    ]));

    $response->assertStatus(200);
    $response->assertSee('-$100.00');
    $response->assertSee('Ticker: SOL');

    // Filter by side long
    $responseLong = $this->actingAs($user)->get(route('pnl-calendar.index', [
        'side' => 'long',
        'year' => now()->year,
        'month' => now()->month,
    ]));

    $responseLong->assertStatus(200);
    $responseLong->assertSee('+$1,000.00');
    $responseLong->assertSee('Long');
});
