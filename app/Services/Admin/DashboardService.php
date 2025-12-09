<?php

namespace App\Services\Admin;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getSummaryMetrics(): array
    {
        $tz  = config('app.timezone', 'Asia/Ho_Chi_Minh');
        $now = Carbon::now($tz);

        $today = $this->aggregateRange(
            $now->copy()->startOfDay(),
            $now->copy()->endOfDay()
        );

        $monthStart = $now->copy()->startOfMonth();
        $monthEnd   = $now->copy()->endOfMonth();

        $month = $this->aggregateRange($monthStart, $monthEnd);

        $prevMonthStart = $monthStart->copy()->subMonthNoOverflow()->startOfMonth();
        $prevMonthEnd   = $monthStart->copy()->subMonthNoOverflow()->endOfMonth();
        $prevMonth      = $this->aggregateRange($prevMonthStart, $prevMonthEnd);

        $year = $this->aggregateRange(
            $now->copy()->startOfYear(),
            $now->copy()->endOfYear()
        );

        $month['revenue_change_percent'] = $this->percentChange(
            $month['revenue_vnd'],
            $prevMonth['revenue_vnd']
        );
        $month['profit_change_percent'] = $this->percentChange(
            $month['profit_vnd'],
            $prevMonth['profit_vnd']
        );
        $month['orders_change_percent'] = $this->percentChange(
            $month['total_orders'],
            $prevMonth['total_orders']
        );

        return [
            'today' => $today,
            'month' => $month,
            'year'  => $year,
        ];
    }

    public function getOrderStatusDistribution(): array
    {
        $tz   = config('app.timezone', 'Asia/Ho_Chi_Minh');
        $now  = Carbon::now($tz);
        $from = $now->copy()->startOfMonth();
        $to   = $now->copy()->endOfMonth();

        $rows = DB::table('orders')
            ->selectRaw('upper(status) as status, count(*) as total')
            ->whereBetween(DB::raw('coalesce(placed_at, created_at)'), [$from, $to])
            ->whereIn(DB::raw('upper(status)'), ['COMPLETED', 'DELIVERY_FAILED', 'RETURNED'])
            ->groupBy(DB::raw('upper(status)'))
            ->get();

        $map = [
            'COMPLETED'       => 0,
            'DELIVERY_FAILED' => 0,
            'RETURNED'        => 0,
        ];

        foreach ($rows as $row) {
            $key = strtoupper((string) $row->status);
            if (isset($map[$key])) {
                $map[$key] = (int) $row->total;
            }
        }

        return [
            'labels' => [
                'Giao thành công',
                'Giao thất bại',
                'Hoàn / trả hàng',
            ],
            'data' => [
                $map['COMPLETED'],
                $map['DELIVERY_FAILED'],
                $map['RETURNED'],
            ],
        ];
    }

    public function getTopCustomers(int $limit = 5): array
    {
        $tz   = config('app.timezone', 'Asia/Ho_Chi_Minh');
        $now  = Carbon::now($tz);
        $from = $now->copy()->subDays(90)->startOfDay();
        $to   = $now->copy()->endOfDay();

        $rows = DB::table('orders as o')
            ->join('users as u', 'u.id', '=', 'o.user_id')
            ->selectRaw(
                'u.id,
                 u.name,
                 u.email,
                 u.phone,
                 count(*) as orders_count,
                 coalesce(sum(o.grand_total_vnd), 0) as total_spent_vnd,
                 coalesce(avg(o.grand_total_vnd), 0) as avg_order_vnd'
            )
            ->whereRaw('upper(o.status) = ?', ['COMPLETED'])
            ->whereBetween(DB::raw('coalesce(o.placed_at, o.created_at)'), [$from, $to])
            ->groupBy('u.id', 'u.name', 'u.email', 'u.phone')
            ->orderByDesc('total_spent_vnd')
            ->limit($limit)
            ->get();

        $result = [];

        foreach ($rows as $row) {
            $result[] = [
                'id'              => (string) $row->id,
                'name'            => (string) $row->name,
                'email'           => $row->email ? (string) $row->email : null,
                'phone'           => $row->phone ? (string) $row->phone : null,
                'orders_count'    => (int) $row->orders_count,
                'total_spent_vnd' => (int) $row->total_spent_vnd,
                'avg_order_vnd'   => (int) $row->avg_order_vnd,
            ];
        }

        return $result;
    }

    public function getTopProducts(int $limit = 5): array
    {
        $tz   = config('app.timezone', 'Asia/Ho_Chi_Minh');
        $now  = Carbon::now($tz);
        $from = $now->copy()->subDays(90)->startOfDay();
        $to   = $now->copy()->endOfDay();

        $rows = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->selectRaw(
                'oi.product_id,
                 max(oi.product_title_snapshot) as product_title,
                 coalesce(sum(oi.quantity), 0) as total_qty,
                 coalesce(sum(oi.total_price_vnd), 0) as revenue_vnd'
            )
            ->whereRaw('upper(o.status) = ?', ['COMPLETED'])
            ->whereBetween(DB::raw('coalesce(o.placed_at, o.created_at)'), [$from, $to])
            ->groupBy('oi.product_id')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get();

        $result = [];

        foreach ($rows as $row) {
            $result[] = [
                'product_id'   => (string) $row->product_id,
                'product_name' => (string) ($row->product_title ?? 'Sản phẩm'),
                'total_qty'    => (int) $row->total_qty,
                'revenue_vnd'  => (int) $row->revenue_vnd,
            ];
        }

        return $result;
    }

    public function getLowStockProducts(int $limit = 5, int $threshold = 5): array
    {
        $rows = DB::table('stocks as s')
            ->join('products as p', 'p.id', '=', 's.product_id')
            ->selectRaw(
                'p.id as product_id,
                 p.title as product_name,
                 s.warehouse_id,
                 s.on_hand,
                 s.reserved'
            )
            ->where('s.on_hand', '<=', $threshold)
            ->orderBy('s.on_hand')
            ->limit($limit)
            ->get();

        $result = [];

        foreach ($rows as $row) {
            $result[] = [
                'product_id'   => (string) $row->product_id,
                'product_name' => (string) $row->product_name,
                'warehouse_id' => (string) $row->warehouse_id,
                'on_hand'      => (int) $row->on_hand,
                'reserved'     => (int) $row->reserved,
            ];
        }

        return $result;
    }

    public function getRevenueSeries(int $months = 12): array
    {
        $tz     = config('app.timezone', 'Asia/Ho_Chi_Minh');
        $now    = Carbon::now($tz)->startOfMonth();
        $labels = [];
        $revenue = [];
        $cogs    = [];
        $profit  = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $from  = $month->copy()->startOfMonth();
            $to    = $month->copy()->endOfMonth();

            $agg = $this->aggregateRange($from, $to);

            $labels[]  = $month->format('m/Y');
            $revenue[] = $agg['revenue_vnd'];
            $cogs[]    = $agg['cogs_vnd'];
            $profit[]  = $agg['profit_vnd'];
        }

        return [
            'labels'  => $labels,
            'revenue' => $revenue,
            'cogs'    => $cogs,
            'profit'  => $profit,
        ];
    }

    public function getTodayOrderPerformance(): array
    {
        $tz  = config('app.timezone', 'Asia/Ho_Chi_Minh');
        $now = Carbon::now($tz);

        $todayFrom = $now->copy()->startOfDay();
        $todayTo   = $now->copy()->endOfDay();

        $yesterdayFrom = $now->copy()->subDay()->startOfDay();
        $yesterdayTo   = $now->copy()->subDay()->endOfDay();

        $todayCounts     = $this->countOrdersByStatusRange($todayFrom, $todayTo);
        $yesterdayCounts = $this->countOrdersByStatusRange($yesterdayFrom, $yesterdayTo);

        $totalToday     = $todayCounts['total'];
        $totalYesterday = $yesterdayCounts['total'];

        return [
            'total_today'       => $totalToday,
            'completed_today'   => $todayCounts['completed'],
            'shipping_today'    => $todayCounts['shipping'],
            'diff_vs_yesterday' => $totalToday - $totalYesterday,
        ];
    }

    private function aggregateRange(Carbon $from, Carbon $to): array
    {
        $row = DB::table('orders as o')
            ->leftJoin('order_items as oi', 'oi.order_id', '=', 'o.id')
            ->leftJoin('order_batches as ob', 'ob.order_item_id', '=', 'oi.id')
            ->whereRaw('upper(o.status) = ?', ['COMPLETED'])
            ->whereBetween(DB::raw('coalesce(o.placed_at, o.created_at)'), [$from, $to])
            ->selectRaw(
                'coalesce(sum(o.grand_total_vnd), 0) as revenue_vnd,
             coalesce(sum(ob.quantity * ob.unit_cost_vnd), 0) as cogs_vnd,
             count(distinct o.id) as completed_orders'
            )
            ->first();

        $revenueBeforeShip = (int) ($row->revenue_vnd ?? 0);
        $cogs              = (int) ($row->cogs_vnd ?? 0);
        $orders            = (int) ($row->completed_orders ?? 0);

        $shippingTotal = 30000 * $orders;
        $revenue       = max($revenueBeforeShip - $shippingTotal, 0);

        $profit = $revenue - $cogs;

        return [
            'revenue_vnd'      => $revenue,
            'cogs_vnd'         => $cogs,
            'profit_vnd'       => $profit,
            'total_orders'     => $orders,
            'delivered_orders' => $orders,
        ];
    }


    private function countOrdersByStatusRange(Carbon $from, Carbon $to): array
    {
        $rows = DB::table('orders')
            ->selectRaw('upper(status) as status, count(*) as total')
            ->whereBetween(DB::raw('coalesce(placed_at, created_at)'), [$from, $to])
            ->groupBy(DB::raw('upper(status)'))
            ->get();

        $total     = 0;
        $completed = 0;
        $shipping  = 0;

        foreach ($rows as $row) {
            $status = strtoupper((string) $row->status);
            $count  = (int) $row->total;

            $total += $count;

            if (in_array($status, ['COMPLETED', 'DELIVERED'], true)) {
                $completed += $count;
            } elseif (in_array($status, ['SHIPPING', 'SHIPPED'], true)) {
                $shipping += $count;
            }
        }

        return [
            'total'     => $total,
            'completed' => $completed,
            'shipping'  => $shipping,
        ];
    }

    private function percentChange(int $current, int $previous): ?float
    {
        if ($previous === 0) {
            return null;
        }

        $delta = $current - $previous;

        return round(($delta / $previous) * 100, 1);
    }
}
