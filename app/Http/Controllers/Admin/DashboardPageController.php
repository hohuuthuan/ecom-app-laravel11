<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService;
use Illuminate\Contracts\View\View;

class DashboardPageController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    public function index(): View
    {
        $metrics          = $this->dashboardService->getSummaryMetrics();
        $orderStatusChart = $this->dashboardService->getOrderStatusDistribution();
        $topCustomers     = $this->dashboardService->getTopCustomers();
        $topProducts      = $this->dashboardService->getTopProducts();
        $lowStockProducts = $this->dashboardService->getLowStockProducts();
        $revenueSeries    = $this->dashboardService->getRevenueSeries();
        $todayPerformance = $this->dashboardService->getTodayOrderPerformance();

        $dashboardData = [
            'metrics'          => $metrics,
            'orderStatusChart' => $orderStatusChart,
            'topCustomers'     => $topCustomers,
            'topProducts'      => $topProducts,
            'lowStockProducts' => $lowStockProducts,
            'revenueSeries'    => $revenueSeries,
            'todayPerformance' => $todayPerformance,
        ];

        return view('admin.dashboard', [
            'metrics'          => $metrics,
            'todayPerformance' => $todayPerformance,
            'dashboardData'    => $dashboardData,
        ]);
    }
}
