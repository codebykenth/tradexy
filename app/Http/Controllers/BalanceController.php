<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BalanceRequest;
use App\Models\Balance;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class BalanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = $this->getBalancesData();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($data);
        }

        return view('balances.index', $data);
    }

    private function getBalancesData(): array
    {
        $accountMode = session('account_mode', 'real');
        $marketMode = session('market_type', 'crypto');

        $query = Balance::where('user_id', Auth::id());

        if ($accountMode !== 'all') {
            $query->where('is_demo', $accountMode === 'demo');
        }

        if ($marketMode !== 'all') {
            $query->where('market', $marketMode);
        }

        $balances = $query->latest('date')->paginate(10);

        // Transform the collection to include formatted attributes for JS
        $balances->getCollection()->transform(function ($balance) {
            $balance->local_date = \Carbon\Carbon::parse($balance->date)->format('M d, Y');

            return $balance;
        });

        return [
            'balances' => $balances,
        ];
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('balances.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BalanceRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Balance::create([
            'user_id' => Auth::id(),
            ...$validated,
        ]);

        return redirect()->route('balances.index')->with('success', 'Balance successfully added.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BalanceRequest $request, string $id): RedirectResponse
    {
        $validated = $request->validated();

        $this->findOwnedBalance($id)->update($validated);

        return redirect()->route('balances.index')->with('success', 'Balance updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $this->findOwnedBalance($id)->delete();

        return redirect()->route('balances.index')->with('success', 'Balance deleted successfully.');
    }

    private function findOwnedBalance(string $id): Balance
    {
        return Balance::where('user_id', Auth::id())->findOrFail($id);
    }
}
