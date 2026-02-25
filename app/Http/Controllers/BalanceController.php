<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Services\BybitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BalanceController extends Controller
{
    public function __construct(private BybitService $bybitService) {}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $balances = Balance::where('user_id', Auth::id())->get();
        return response()->json([
            'data' => $balances
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function testBalance() {
        return $this->bybitService->getAccountBalance();
    }
}
