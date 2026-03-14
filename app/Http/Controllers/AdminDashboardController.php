<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Balance;
use App\Models\Trade;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): View
    {
        // 1. User Metrics
        $totalUsers = User::count();
        $newUsersThisWeek = User::where('created_at', '>=', now()->startOfWeek())->count();
        $activeToday = User::where('last_seen_at', '>=', now()->startOfDay())->count();
        $activeNow = User::where('last_seen_at', '>=', now()->subMinutes(10))->count();

        // 2. System Usage Metrics
        $totalTrades = Trade::count();
        $totalBalances = Balance::count();
        $tradesToday = Trade::where('created_at', '>=', now()->startOfDay())->count();

        // 3. Recent Activity Log
        $recentLogs = ActivityLog::with('user')
            ->latest()
            ->limit(10)
            ->get();

        // 4. Activity Chart Data (last 7 days)
        $activities = ActivityLog::where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'newUsersThisWeek',
            'activeToday',
            'activeNow',
            'totalTrades',
            'totalBalances',
            'tradesToday',
            'recentLogs',
            'activities'
        ));
    }

    /**
     * Display the user list.
     */
    public function users(): View
    {
        $users = User::latest()
            ->paginate(20);

        return view('admin.users', compact('users'));
    }

    /**
     * Display the activity log list.
     */
    public function logs(Request $request): View
    {
        $query = ActivityLog::with('user');

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $logs = $query->latest()->paginate(50);

        return view('admin.logs', compact('logs'));
    }
}
