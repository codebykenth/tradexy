<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TradeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ownedTrades = Trade::where('user_id', '=', Auth::id())->latest('close_datetime')->paginate(10);

        return view('trades.index', [
            "ownedTrades" => $ownedTrades
        ]);
    }

    public function create()
    {
        return view('trades.create');
    }

    public function show($id)
    {
        $trade = Trade::where('user_id', Auth::id())->with(['strategy', 'lessons', 'reasons'])->findOrFail($id);
        return view('trades.show', ["trade" => $trade]);
    }

    public function edit($id)
    {
        $trade = Trade::where('user_id', Auth::id())->with(['strategy', 'lessons', 'reasons'])->findOrFail($id);
        return view('trades.edit', ['trade' => $trade]);
    }

    public function store(Request $request)
    {

    }

    public function update(Request $request, $id)
    {

    }

    public function destroy($id)
    {

    }
}
