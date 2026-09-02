<?php

declare(strict_types=1);

use App\Models\Balance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

test('authenticated user can download the balance import CSV template', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create(['terms_accepted_at' => now()]);

    $response = $this->actingAs($user)->get(route('balances.template'));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $response->assertHeader('content-disposition', 'attachment; filename="tradexy_balance_import_template.csv"');
});

test('authenticated user can export balances as CSV', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create(['terms_accepted_at' => now()]);

    Balance::create([
        'user_id' => $user->id,
        'date' => now()->subDay(),
        'market' => 'crypto',
        'wallet_balance' => 10000,
        'total_equity' => 10500,
        'cum_realised_pnl' => 500,
        'is_demo' => false,
    ]);

    $response = $this->actingAs($user)->get(route('balances.export'));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    expect($response->headers->get('content-disposition'))->toContain('balances_export_');
});

test('authenticated user can filter balances by date range', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create(['terms_accepted_at' => now()]);

    // Balance 1: Feb 20, 2026
    Balance::create([
        'user_id' => $user->id,
        'date' => '2026-02-20 00:00:00',
        'market' => 'crypto',
        'wallet_balance' => 9000,
        'total_equity' => 9200,
        'cum_realised_pnl' => 200,
        'is_demo' => false,
    ]);

    // Balance 2: March 5, 2026
    Balance::create([
        'user_id' => $user->id,
        'date' => '2026-03-05 00:00:00',
        'market' => 'crypto',
        'wallet_balance' => 10000,
        'total_equity' => 10500,
        'cum_realised_pnl' => 500,
        'is_demo' => false,
    ]);

    // Filter for March balances only
    $response = $this->actingAs($user)->get(route('balances.index', [
        'start_date' => '2026-03-01',
        'end_date' => '2026-03-31',
    ]));

    $response->assertStatus(200);
    $balances = $response->viewData('balances');
    expect($balances->total())->toBe(1);
    expect((float) $balances->first()->wallet_balance)->toBe(10000.0);
});

test('authenticated user can filter balances by pnl trend and equity range', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create(['terms_accepted_at' => now()]);

    // Balance 1: Profit day, high equity
    Balance::create([
        'user_id' => $user->id,
        'date' => '2026-03-01 00:00:00',
        'market' => 'crypto',
        'wallet_balance' => 20000,
        'total_equity' => 22000,
        'cum_realised_pnl' => 2000,
        'is_demo' => false,
    ]);

    // Balance 2: Loss day, lower equity
    Balance::create([
        'user_id' => $user->id,
        'date' => '2026-03-02 00:00:00',
        'market' => 'crypto',
        'wallet_balance' => 15000,
        'total_equity' => 14000,
        'cum_realised_pnl' => -1000,
        'is_demo' => false,
    ]);

    // Filter by profit trend
    $respProfit = $this->actingAs($user)->get(route('balances.index', ['pnl_trend' => 'profit']));
    $respProfit->assertStatus(200);
    $balancesProfit = $respProfit->viewData('balances');
    expect($balancesProfit->total())->toBe(1);
    expect((float) $balancesProfit->first()->cum_realised_pnl)->toBe(2000.0);

    // Filter by equity range
    $respEquity = $this->actingAs($user)->get(route('balances.index', ['min_equity' => 20000]));
    $respEquity->assertStatus(200);
    $balancesEquity = $respEquity->viewData('balances');
    expect($balancesEquity->total())->toBe(1);
    expect((float) $balancesEquity->first()->total_equity)->toBe(22000.0);
});

test('authenticated user can import valid balances from CSV', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create(['terms_accepted_at' => now()]);

    $csvContent = "date,market,wallet_balance,total_equity,cum_realised_pnl,is_demo\n"
        ."2026-03-01,crypto,10000.00,10500.00,500.00,0\n"
        ."2026-03-02,pse,150000.00,158000.00,8000.00,0\n";

    $file = UploadedFile::fake()->createWithContent('balances.csv', $csvContent);

    $response = $this->actingAs($user)->post(route('balances.import'), [
        'file' => $file,
    ]);

    $response->assertRedirect(route('balances.index'));
    $response->assertSessionHas('success');

    expect(Balance::where('user_id', $user->id)->count())->toBe(2);

    $crypto = Balance::where('user_id', $user->id)->where('market', 'crypto')->first();
    expect($crypto)->not->toBeNull();
    expect((float) $crypto->wallet_balance)->toBe(10000.0);
    expect((float) $crypto->total_equity)->toBe(10500.0);
    expect((float) $crypto->cum_realised_pnl)->toBe(500.0);
});

test('balance import keeps only 1 copy when duplicate entries exist in the same CSV file', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create(['terms_accepted_at' => now()]);

    // CSV containing 3 duplicate rows for the same date/market/demo status
    $csvContent = "date,market,wallet_balance,total_equity,cum_realised_pnl,is_demo\n"
        ."2026-03-01,crypto,10000.00,10500.00,500.00,0\n"
        ."2026-03-01,crypto,10000.00,10500.00,500.00,0\n"
        ."2026-03-01,crypto,10000.00,10500.00,500.00,0\n"
        ."2026-03-02,crypto,11000.00,11800.00,1800.00,0\n";

    $file = UploadedFile::fake()->createWithContent('balances_duplicate.csv', $csvContent);

    $response = $this->actingAs($user)->post(route('balances.import'), [
        'file' => $file,
    ]);

    $response->assertRedirect(route('balances.index'));
    $response->assertSessionHas('success');

    // Exactly 2 balances created (1 for 2026-03-01 and 1 for 2026-03-02), duplicate entries skipped
    expect(Balance::where('user_id', $user->id)->count())->toBe(2);
});

test('balance import skips duplicate entry that already exists in the database', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create(['terms_accepted_at' => now()]);

    Balance::create([
        'user_id' => $user->id,
        'date' => '2026-03-01 00:00:00',
        'market' => 'crypto',
        'wallet_balance' => 10000,
        'total_equity' => 10500,
        'cum_realised_pnl' => 500,
        'is_demo' => false,
    ]);

    $csvContent = "date,market,wallet_balance,total_equity,cum_realised_pnl,is_demo\n"
        ."2026-03-01,crypto,10000.00,10500.00,500.00,0\n"
        ."2026-03-03,crypto,12000.00,12500.00,2500.00,0\n";

    $file = UploadedFile::fake()->createWithContent('balances_exist.csv', $csvContent);

    $response = $this->actingAs($user)->post(route('balances.import'), [
        'file' => $file,
    ]);

    $response->assertRedirect(route('balances.index'));
    $response->assertSessionHas('success');

    // Total in DB should be 2 (existing 1 + new 1)
    expect(Balance::where('user_id', $user->id)->count())->toBe(2);
});

test('balance import fails when required columns are missing', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create(['terms_accepted_at' => now()]);

    $csvContent = "invalid_col1,invalid_col2\n100,200\n";
    $file = UploadedFile::fake()->createWithContent('invalid_balance.csv', $csvContent);

    $response = $this->actingAs($user)->post(route('balances.import'), [
        'file' => $file,
    ]);

    $response->assertSessionHas('error');
    expect(Balance::where('user_id', $user->id)->count())->toBe(0);
});
