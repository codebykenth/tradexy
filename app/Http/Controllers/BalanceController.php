<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Services\BybitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BalanceController extends Controller
{
    public function __construct(private BybitService $bybitService)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $balances = Balance::where('user_id', Auth::id())->latest('date')->paginate(10);
        return view('balances.index', [
            'balances' => $balances
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('balances.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'date' => 'date|required',
                'wallet_balance' => 'numeric|required',
                'total_equity' => 'numeric|required',
                'cum_realised_pnl' => 'numeric|required'
            ]
        );

        Balance::create($validated);

        return redirect()->route('balances.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $balance = Balance::findOrFail($id);
        return view('balances.show', ['balance' => $balance]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('balances.edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate(
            [
                'date' => 'date',
                'wallet_balance' => 'numeric',
                'total_equity' => 'numeric',
                'cum_realised_pnl' => 'numeric'
            ]
        );

        $balance = Balance::findOrFail($id);
        $balance->update($validated);

        return redirect()->route('balances.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $balance = Balance::findOrFail($id);
        $balance->delete();

        return redirect()->route('balances.index');
    }

    public function testBalance()
    {
        $balance = $this->bybitService->getAccountBalance()['result']['list'][0];
        $usdtData = $balance['coin'][0];

        return $usdtData;
    }
}
