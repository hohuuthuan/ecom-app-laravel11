<?php

namespace App\Services\Admin;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getRevenueReport(array $filters = []): array
    {
        $tz  = config('app.timezone', 'Asia/Ho_Chi_Minh');
        $now = Carbon::now($tz);

        $from = !empty($filters['from'])
            ? Carbon::parse($filters['from'], $tz)->startOfDay()
            : $now->copy()->subDays(30)->startOfDay();

        $to = !empty($filters['to'])
            ? Carbon::parse($filters['to'], $tz)->endOfDay()
            : $now->copy()->endOfDay();

        $groupBy = $filters['group_by'] ?? 'day';
        if (!in_array($groupBy, ['day', 'month', 'year'], true)) {
            $groupBy = 'day';
        }
        $dateCol = 'o.delivered_at';

        if ($groupBy === 'month') {
            $periodExpr = "DATE_FORMAT({$dateCol}, '%Y-%m-01')";
            $labelExpr  = "DATE_FORMAT({$dateCol}, '%m/%Y')";
        } elseif ($groupBy === 'year') {
            $periodExpr = "DATE_FORMAT({$dateCol}, '%Y-01-01')";
            $labelExpr  = "DATE_FORMAT({$dateCol}, '%Y')";
        } else {
            $periodExpr = "DATE({$dateCol})";
            $labelExpr  = "DATE_FORMAT({$dateCol}, '%d/%m/%Y')";
        }
        $perOrder = DB::table('orders as o')
            ->whereRaw('UPPER(o.status) = ?', ['completed'])
            ->whereRaw('UPPER(o.payment_status) = ?', ['paid'])
            ->whereBetween($dateCol, [$from, $to])
            ->selectRaw("
            o.id as order_id,
            {$periodExpr} as period_key,
            {$labelExpr} as period_label,
            (COALESCE(o.grand_total_vnd, 0) - COALESCE(o.shipping_fee_vnd, 0)) as revenue_vnd,
            (
                select COALESCE(SUM(ob.quantity * ob.unit_cost_vnd), 0)
                from order_items oi
                join order_batches ob on ob.order_item_id = oi.id
                where oi.order_id = o.id
            ) as cogs_vnd
        ");

        $rows = DB::query()
            ->fromSub($perOrder, 't')
            ->selectRaw("
            t.period_key,
            t.period_label,
            COALESCE(SUM(t.revenue_vnd), 0) as revenue_vnd,
            COALESCE(SUM(t.cogs_vnd), 0) as cogs_vnd
        ")
            ->groupBy('t.period_key', 't.period_label')
            ->orderBy('t.period_key')
            ->get();

        $labels  = [];
        $revenue = [];
        $cogs    = [];
        $profit  = [];
        $table   = [];

        foreach ($rows as $row) {
            $rev    = (int) ($row->revenue_vnd ?? 0);
            $cog    = (int) ($row->cogs_vnd ?? 0);
            $pro    = $rev - $cog;
            $margin = $rev > 0 ? round($pro * 100 / $rev, 1) : null;

            $labels[]  = (string) $row->period_label;
            $revenue[] = $rev;
            $cogs[]    = $cog;
            $profit[]  = $pro;

            $table[] = [
                'period'      => (string) $row->period_label,
                'revenue_vnd' => $rev,
                'cogs_vnd'    => $cog,
                'profit_vnd'  => $pro,
                'margin_pct'  => $margin,
            ];
        }

        return [
            'chart' => [
                'labels'  => $labels,
                'revenue' => $revenue,
                'cogs'    => $cogs,
                'profit'  => $profit,
            ],
            'table' => $table,
        ];
    }


    public function getOrderReport(array $filters = []): array
    {
        $tz  = config('app.timezone', 'Asia/Ho_Chi_Minh');
        $now = Carbon::now($tz);

        $from = !empty($filters['from'])
            ? Carbon::parse($filters['from'], $tz)->startOfDay()
            : null;

        $to = !empty($filters['to'])
            ? Carbon::parse($filters['to'], $tz)->endOfDay()
            : null;

        $status = $filters['status'] ?? null;
        if ($status !== null && $status !== '') {
            $status = strtoupper($status);
        }

        $rows = DB::table('orders as o')
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->whereRaw('UPPER(o.payment_status) = ?', ['paid'])
            ->when($status, function ($q) use ($status) {
                return $q->whereRaw('UPPER(o.status) = ?', [$status]);
            }, function ($q) {
                return $q->whereRaw('UPPER(o.status) = ?', ['completed']);
            })
            ->when($from, function ($q) use ($from) {
                return $q->where('o.delivered_at', '>=', $from);
            })
            ->when($to, function ($q) use ($to) {
                return $q->where('o.delivered_at', '<=', $to);
            })
            ->selectRaw('
                    o.id,
                    o.code,
                    o.status,
                    o.delivered_at,
                    u.name as customer_name,
                    COALESCE(o.grand_total_vnd - o.shipping_fee_vnd, 0) as revenue_vnd,
                    (
                        select COALESCE(SUM(ob.quantity * ob.unit_cost_vnd), 0)
                        from order_items oi
                        join order_batches ob on ob.order_item_id = oi.id
                        where oi.order_id = o.id
                    ) as cogs_vnd
                ')
            ->orderByDesc('o.delivered_at')
            ->limit(200)
            ->get();

        $result = [];

        foreach ($rows as $row) {
            $rev = (int) ($row->revenue_vnd ?? 0);
            $cog = (int) ($row->cogs_vnd ?? 0);

            $result[] = [
                'code'          => (string) $row->code,
                'customer_name' => $row->customer_name ? (string) $row->customer_name : 'Khách lẻ',
                'delivered_at'  => $row->delivered_at ? (string) $row->delivered_at : null,
                'status'        => (string) $row->status,
                'revenue_vnd'   => $rev,
                'cogs_vnd'      => $cog,
                'profit_vnd'    => $rev - $cog,
            ];
        }

        return $result;
    }

    public function getBatchReport(array $filters = []): array
    {
        $tz = config('app.timezone', 'Asia/Ho_Chi_Minh');

        $from = !empty($filters['from'])
            ? Carbon::parse($filters['from'], $tz)->startOfDay()
            : null;

        $productKeyword = $filters['product'] ?? null;

        $rows = DB::table('batches as b')
            ->join('products as p', 'p.id', '=', 'b.product_id')
            ->join('warehouses as w', 'w.id', '=', 'b.warehouse_id')
            ->leftJoin('purchase_receipt_items as pri', 'pri.id', '=', 'b.purchase_receipt_item_id')
            ->leftJoin('purchase_receipts as pr', 'pr.id', '=', 'pri.purchase_receipt_id')
            ->leftJoin('batch_stocks as bs', 'bs.batch_id', '=', 'b.id')
            ->leftJoin('order_batches as ob', 'ob.batch_id', '=', 'b.id')
            ->leftJoin('order_items as oi', 'oi.id', '=', 'ob.order_item_id')
            ->leftJoin('orders as o', 'o.id', '=', 'oi.order_id')
            ->when($productKeyword, function ($q) use ($productKeyword) {
                $kw = '%' . $productKeyword . '%';
                return $q->where(function ($sub) use ($kw) {
                    $sub->where('p.title', 'like', $kw)
                        ->orWhere('p.code', 'like', $kw);
                });
            })
            ->when($from, function ($q) use ($from) {
                return $q->where('pr.received_at', '>=', $from);
            })
            ->selectRaw("
                    p.id   as product_id,
                    p.title as product_name,
                    p.code  as product_code,
                    b.id    as batch_id,
                    w.name  as warehouse_name,
                    pr.receipt_code,
                    pr.received_at,
                    b.quantity          as qty_import,
                    COALESCE(MAX(bs.on_hand), 0) as qty_on_hand,
                    b.import_price_vnd  as unit_cost_vnd,
                    COALESCE(
                        SUM(
                            CASE
                                WHEN UPPER(o.payment_status) = 'paid'
                                AND UPPER(o.status) = 'completed'
                                THEN ob.quantity
                                ELSE 0
                            END
                        ),
                        0
                    ) as qty_sold,
                    COALESCE(
                        SUM(
                            CASE
                                WHEN UPPER(o.payment_status) = 'paid'
                                AND UPPER(o.status) = 'completed'
                                THEN ob.quantity * ob.unit_cost_vnd
                                ELSE 0
                            END
                        ),
                        0
                    ) as cogs_sold_vnd,
                    COALESCE(
                        SUM(
                            CASE
                                WHEN UPPER(o.payment_status) = 'paid'
                                AND UPPER(o.status) = 'completed'
                                THEN ob.quantity * (oi.total_price_vnd / NULLIF(oi.quantity, 0))
                                    - COALESCE(o.shipping_fee_vnd * (ob.quantity * (oi.total_price_vnd / NULLIF(oi.quantity, 0)) / NULLIF(o.grand_total_vnd, 0)), 0)
                                ELSE 0
                            END
                        ),
                        0
                    ) as revenue_vnd
                ")
            ->groupBy(
                'p.id',
                'p.title',
                'p.code',
                'b.id',
                'w.name',
                'pr.receipt_code',
                'pr.received_at',
                'b.quantity',
                'b.import_price_vnd'
            )
            ->orderBy('p.title')
            ->orderByDesc('pr.received_at')
            ->limit(300)
            ->get();

        $result = [];

        foreach ($rows as $row) {
            $qtyImport   = (int) ($row->qty_import ?? 0);
            $qtyOnHand   = (int) ($row->qty_on_hand ?? 0);
            $qtySold     = (int) ($row->qty_sold ?? 0);
            $unitCost    = (int) ($row->unit_cost_vnd ?? 0);
            $revenue     = (int) ($row->revenue_vnd ?? 0);
            $cogsSold    = (int) ($row->cogs_sold_vnd ?? 0);

            if ($cogsSold === 0 && $qtySold > 0 && $unitCost > 0) {
                $cogsSold = $qtySold * $unitCost;
            }

            $totalImportCost = $qtyImport * $unitCost;
            $profit          = $revenue - $cogsSold;

            if ($totalImportCost > 0) {
                $roiPct = round($profit * 100 / $totalImportCost, 1);
            } else {
                $roiPct = null;
            }

            if ($qtySold === 0) {
                $roiStatus = 'Chưa bán';
            } elseif ($revenue <= 0) {
                $roiStatus = 'Chưa hoàn vốn';
            } elseif ($revenue < $totalImportCost) {
                $roiStatus = 'Chưa hoàn vốn';
            } elseif ($profit < 0) {
                $roiStatus = 'Lỗ';
            } else {
                $roiStatus = 'Đã hoàn vốn';
            }

            $result[] = [
                'product_id'            => (string) $row->product_id,
                'product_name'          => (string) $row->product_name,
                'product_code'          => $row->product_code ? (string) $row->product_code : null,
                'batch_id'              => (string) $row->batch_id,
                'warehouse_name'        => (string) $row->warehouse_name,
                'receipt_code'          => $row->receipt_code ? (string) $row->receipt_code : null,
                'received_at'           => $row->received_at ? (string) $row->received_at : null,
                'qty_import'            => $qtyImport,
                'qty_sold'              => $qtySold,
                'qty_available'         => $qtyOnHand,
                'unit_cost_vnd'         => $unitCost,
                'total_import_cost_vnd' => $totalImportCost,
                'revenue_vnd'           => $revenue,
                'cogs_vnd'              => $cogsSold,
                'profit_vnd'            => $profit,
                'roi_pct'               => $roiPct,
                'roi_status'            => $roiStatus,
            ];
        }

        return $result;
    }
}
