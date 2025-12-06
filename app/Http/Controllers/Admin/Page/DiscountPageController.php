<?php

namespace App\Http\Controllers\Admin\Page;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use Illuminate\Http\Request;

class DiscountPageController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page_discount', 20);

        $query = Discount::query()
            ->withCount([
                'usages as total_used' => function ($q) {
                    $q->whereNotNull('used_at');
                },
            ])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        $discounts = $query->paginate($perPage)->withQueryString();

        return view('admin.discount.index', compact('discounts'));
    }


    public function create()
    {
        return view('admin.discount.create');
    }

    // public function show(string $id)
    // {
    //     $discount = Discount::findOrFail($id);

    //     return view('admin.discount.show', compact('discount'));
    // }

    public function edit(string $id)
    {
        $discount = Discount::findOrFail($id);

        return view('admin.discount.edit', compact('discount'));
    }
}
