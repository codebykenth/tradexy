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
}
