<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('Super Admin');

        $orderQuery = Order::query();
        $customerQuery = Customer::query();
        $period = $request->input('period', 'today');

        // Role-based filtering
        if (!$isSuperAdmin) {
            $orderQuery->where('created_by', $user->id);
            $customerQuery->where('created_by', $user->id);
        }

        // Date handling
        $startDate = null;
        $endDate = null;

        switch ($period) {
            case 'today':
                $startDate = now()->startOfDay();
                break;
            case 'yesterday':
                $startDate = now()->subDay()->startOfDay();
                $endDate = now()->subDay()->endOfDay();
                break;
            case 'week':
                $startDate = now()->startOfWeek();
                break;
            case 'month':
                $startDate = now()->startOfMonth();
                break;
            case 'year':
                $startDate = now()->startOfYear();
                break;
            default:
                $startDate = now()->startOfDay();
                $period = 'today';
        }

        $filteredOrders = clone $orderQuery;
        $filteredCustomers = clone $customerQuery;

        // Apply date filter
        if (in_array($period, ['today', 'yesterday'])) {
            $targetDate = $period === 'today' ? now() : now()->subDay();
            $filteredOrders->whereDate('created_at', $targetDate);
            $filteredCustomers->whereDate('created_at', $targetDate);
        } else {
            $filteredOrders->where('created_at', '>=', $startDate);
            $filteredCustomers->where('created_at', '>=', $startDate);

            if ($endDate) {
                $filteredOrders->where('created_at', '<=', $endDate);
                $filteredCustomers->where('created_at', '<=', $endDate);
            }
        }

        // 🔥 Optimized single aggregation query
        $statsData = (clone $filteredOrders)
            ->selectRaw("
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                SUM(CASE WHEN status NOT IN ('cancelled','scheduled') THEN grand_total ELSE 0 END) as total_sales
            ")
            ->first();

        $ordersCount = (int) $statsData->total_orders;
        $cancelledCount = (int) $statsData->cancelled_orders;
        $totalSales = (float) $statsData->total_sales;

        $customersCount = (clone $filteredCustomers)->count();

        // Previous period comparison
        $duration = $startDate->diffInDays($endDate ?? now()) + 1;

        $compareStart = (clone $startDate)->subDays($duration);
        $compareEnd = $endDate
            ? (clone $endDate)->subDays($duration)
            : (clone $startDate)->subSecond();

        $prevQuery = clone $orderQuery;

        if (in_array($period, ['today', 'yesterday'])) {
            $compareDate = $period === 'today' ? now()->subDay() : now()->subDays(2);
            $prevQuery->whereDate('created_at', $compareDate);
        } else {
            $prevQuery->whereBetween('created_at', [$compareStart, $compareEnd]);
        }

        $prevSales = (float) $prevQuery
            ->whereNotIn('status', ['cancelled', 'scheduled'])
            ->sum('grand_total');

        $salesChange = $prevSales > 0
            ? (($totalSales - $prevSales) / $prevSales) * 100
            : ($totalSales > 0 ? 100 : 0);

        $periodLabel = match ($period) {
            'today' => 'yesterday',
            'yesterday' => 'day before',
            'week' => 'last week',
            'month' => 'last month',
            'year' => 'last year',
            default => 'previous ' . $duration . ' days',
        };

        $stats = [
            [
                'title' => 'Total Sales',
                'value' => 'Rs ' . number_format($totalSales, 2),
                'change' => ($salesChange >= 0 ? '+' : '') . number_format($salesChange, 1) . '%',
                'trend' => $salesChange >= 0 ? 'up' : 'down',
                'desc' => 'vs. ' . $periodLabel,
                'icon' => 'dollar-sign'
            ],
            [
                'title' => 'Total Cancelled Orders',
                'value' => number_format($cancelledCount),
                'change' => '',
                'trend' => 'down',
                'desc' => 'In selected period',
                'icon' => 'x-circle'
            ],
            [
                'title' => 'Orders',
                'value' => number_format($ordersCount),
                'change' => '',
                'trend' => 'up',
                'desc' => 'In selected period',
                'icon' => 'shopping-cart'
            ],
            [
                'title' => 'New Customers',
                'value' => number_format($customersCount),
                'change' => '',
                'trend' => 'up',
                'desc' => 'In selected period',
                'icon' => 'users'
            ],
        ];

        // ✅ SAFE eager loading (NO column errors)
        $recentOrders = (clone $filteredOrders)
            ->with(['customer', 'creator'])
            ->latest()
            ->limit(5)
            ->get();

        // Chart data optimized
        $chartQuery = clone $orderQuery;

        if (in_array($period, ['today', 'yesterday'])) {
            $chartDate = $period === 'today' ? now() : now()->subDay();
            $chartQuery->whereDate('created_at', $chartDate);
        } else {
            $chartQuery->whereBetween('created_at', [$startDate, $endDate ?? now()]);
        }

        $chartRaw = $chartQuery
            ->whereNotIn('status', ['cancelled', 'scheduled'])
            ->selectRaw("DATE(created_at) as date, SUM(grand_total) as total")
            ->groupBy('date')
            ->pluck('total', 'date');

        $chartData = [];
        $days = $startDate->diffInDays($endDate ?? now());

        for ($i = $days; $i >= 0; $i--) {
            $date = ($endDate ?? now())->copy()->subDays($i)->format('Y-m-d');
            $chartData[] = (float) ($chartRaw[$date] ?? 0);
        }

        $orderHistory = (clone $filteredOrders)
            ->with(['customer', 'creator', 'items.product'])
            ->latest()
            ->limit(20)
            ->get();

        // Online users
        $onlineUsers = \App\Models\User::query()
            ->withCount([
                'orders' => fn($q) => $q->whereDate('created_at', now()),
                'customers'
            ])
            ->withSum([
                'orders as total_revenue' => fn($q) =>
                    $q->whereDate('created_at', now())
                      ->whereNotIn('status', ['scheduled', 'cancelled'])
            ], 'grand_total')
            ->where('last_seen_at', '>', now()->subMinutes(5))
            ->orderByDesc('last_seen_at')
            ->get();

        return view('dashboard', compact(
            'stats',
            'recentOrders',
            'chartData',
            'orderHistory',
            'period',
            'onlineUsers'
        ));
    }

public function exportTeamActivity()
{
    $today = now();

    $users = \App\Models\User::where('status', 'active')
    ->whereNull('deleted_at') // ✅ added this line only
    ->role('CSR')
    ->withCount([
        // ✅ Only today's orders
        'orders as orders_count' => function ($q) use ($today) {
            $q->whereDate('created_at', $today);
        },

        // ✅ Only today's customers
        'customers as customers_count' => function ($q) use ($today) {
            $q->whereDate('created_at', $today);
        }
    ])->withSum([
        // ✅ Only today's revenue
        'orders as total_revenue' => function ($q) use ($today) {
            $q->whereDate('created_at', $today)
              ->whereNotIn('status', ['scheduled', 'cancelled']);
        }
    ], 'grand_total')
    ->get();

    // CSV Headers
    $csvData = [];
    $csvData[] = ['Name', 'Email', 'Location', 'Orders (Today)', 'Customers (Today)', 'Revenue (Today)'];

    foreach ($users as $user) {
        $csvData[] = [
            $user->name,
            $user->email ?? '',
            $user->location ?? 'Unknown',
            $user->orders_count ?? 0,
            $user->customers_count ?? 0,
            $user->total_revenue ?? 0
        ];
    }

    $filename = "team_activity_today.csv";

    $handle = fopen('php://temp', 'r+');

    foreach ($csvData as $row) {
        fputcsv($handle, $row);
    }

    rewind($handle);

    return response(stream_get_contents($handle), 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="'.$filename.'"',
    ]);
}
}