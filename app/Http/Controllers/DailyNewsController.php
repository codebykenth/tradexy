<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MarketNews;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class DailyNewsController extends Controller
{
    /**
     * Display a listing of the daily news.
     */
    public function index(): View
    {
        $allNews = MarketNews::latest()->paginate(10);
        
        return view('daily-news.index', compact('allNews'));
    }

    /**
     * Display the specified daily news.
     */
    public function show(int $id): View
    {
        $news = MarketNews::findOrFail($id);
        
        return view('daily-news.show', compact('news'));
    }

    /**
     * Display the latest daily news directly.
     */
    public function latest(): View
    {
        $news = MarketNews::latest()->firstOrFail();
        
        return view('daily-news.show', compact('news'));
    }
}
