<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MigrateBalancesController extends Controller
{
    public function migrate()
    {
        $apiToken = config('services.old_journal.token');
        $apiUrl = config('services.old_journal.url');
        $user = User::where('email', config('services.bybit.user_email'))->first();

        $response = Http::withToken($apiToken)->get("$apiUrl/balances")->json();

        $oldBalances = $response['data'];

        foreach ($oldBalances as $balance) {
            Balance::updateOrCreate([
                'user_id' => $user->id,
                'date' => $balance['date']
            ], [
                'total_equity' => $balance['total_equity'],
                'wallet_balance' => $balance['wallet_balance'],
                'cum_realised_pnl' => $balance['cum_realised_pnl'],
            ]);
        }
        
        return response()->json([
            'message' => 'Migration complete!',
            'total_from_api' => count($oldBalances)
        ]);
    }
}
