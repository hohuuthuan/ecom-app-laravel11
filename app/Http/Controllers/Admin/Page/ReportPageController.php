<?php

namespace App\Http\Controllers\Admin\Page;

use App\Http\Controllers\Controller;
use App\Services\Admin\ReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReportPageController extends Controller
{
    private ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request): View
    {
        $activeTab = $request->query('tab', 'revenue');
        $validTabs = ['revenue', 'order', 'batch'];
        if (!in_array($activeTab, $validTabs, true)) {
            $activeTab = 'revenue';
        }

        $revenueFilters = [
            'from'     => $request->query('revenue_from'),
            'to'       => $request->query('revenue_to'),
            'group_by' => $request->query('revenue_group_by', 'day'),
        ];

        $orderFilters = [
            'from'   => $request->query('order_from'),
            'to'     => $request->query('order_to'),
            'status' => $request->query('order_status'),
        ];

        $batchFilters = [
            'product' => $request->query('batch_product'),
            'from'    => $request->query('batch_from'),
        ];

        $revenueReport = $this->reportService->getRevenueReport($revenueFilters);
        $orderRows     = $this->reportService->getOrderReport($orderFilters);
        $batchRows     = $this->reportService->getBatchReport($batchFilters);

        return view('admin.reports.index', [
            'activeTab'      => $activeTab,
            'revenueReport'  => $revenueReport,
            'revenueFilters' => $revenueFilters,
            'orderRows'      => $orderRows,
            'orderFilters'   => $orderFilters,
            'batchRows'      => $batchRows,
            'batchFilters'   => $batchFilters,
        ]);
    }
}
