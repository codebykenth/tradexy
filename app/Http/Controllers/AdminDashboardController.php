<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Balance;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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

    /**
     * Toggle maintenance mode.
     */
    public function toggleMaintenance(): RedirectResponse
    {
        if (app()->isDownForMaintenance()) {
            Artisan::call('up');
            
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'maintenance_off',
                'description' => 'Site taken OUT of maintenance mode.',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->back()->with('success', 'Site is now LIVE.');
        }

        // Secret allows the admin to bypass maintenance mode
        // We set a long random string that is stored in the maintenance file
        $secret = Str::random(32);
        
        Artisan::call('down', [
            '--secret' => $secret,
            '--refresh' => 15, // Refresh browser every 15s for users seeing the maintenance page
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'maintenance_on',
            'description' => 'Site put INTO maintenance mode.',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Redirect to the secret URL so the admin gets the bypass cookie
        return redirect()->to(url($secret))->with('success', 'Maintenance mode ENABLED. You have been granted a bypass cookie.');
    }

    /**
     * Flush all system caches.
     */
    public function flushCache(): RedirectResponse
    {
        Artisan::call('optimize:clear');

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'cache_flush',
            'description' => 'System cache was flushed manually via admin dashboard.',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->back()->with('success', 'System cache flushed successfully.');
    }
}
