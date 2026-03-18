<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MarketNews;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

final class DailyNewsController extends Controller
{
    /**
     * Display a listing of the daily news.
     */
    public function index(): View
    {
        $page = request()->get('page', 1);
        $cacheKey = "daily_news_index_page_{$page}";

        $allNews = Cache::remember($cacheKey, now()->addHours(1), function () use ($page) {
            $query = MarketNews::latest();

            // Get total count for pagination
            $total = $query->count();

            // Get items for current page
            $perPage = 10;
            $items = (clone $query)
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            // Manually create paginator from cached data
            return new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        });

        return view('daily-news.index', compact('allNews'));
    }

    /**
     * Display the specified daily news.
     */
    public function show(int $id): View
    {
        $cacheKey = "daily_news_show_{$id}";

        $news = Cache::remember($cacheKey, now()->addHours(6), function () use ($id) {
            return MarketNews::findOrFail($id);
        });

        return view('daily-news.show', compact('news'));
    }

    /**
     * Display the latest daily news directly.
     */
    public function latest(): View
    {
        $cacheKey = 'daily_news_latest';

        $news = Cache::remember($cacheKey, now()->addHours(1), function () {
            return MarketNews::latest()->firstOrFail();
        });

        return view('daily-news.show', compact('news'));
    }
}
