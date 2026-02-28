<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BalanceRequest;
use App\Models\Balance;
use App\Services\BybitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class BalanceController extends Controller
{
    public function __construct(private readonly BybitService $bybitService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $balances = Balance::where('user_id', Auth::id())->latest('date')->paginate(10);

        return view('balances.index', [
            'balances' => $balances,
        ]);
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
