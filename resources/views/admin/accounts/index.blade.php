@extends('layouts.admin')

@section('title','Admin Panel • Quản lý tài khoản')
@section('page_title','Bảng điều khiển')

@section('breadcrumb')
<nav class="mb-4 text-sm text-gray-500">
  <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Admin</a>
  <span class="mx-1 text-gray-400">/</span>
  <span class="text-gray-700">Quản lý tài khoản</span>
</nav>
@endsection

@section('content')
<div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-1 xl:grid-cols-3">
  <div class="rounded-2xl border border-gray-200 bg-white shadow-soft">
    <div class="p-4">
      <div class="text-xs text-gray-500">Tổng tài khoản</div>
      <div class="mt-1 text-2xl font-semibold">1,284</div>
    </div>
  </div>
  <div class="rounded-2xl border border-gray-200 bg-white shadow-soft">
    <div class="p-4">
      <div class="text-xs text-gray-500">Đang hoạt động</div>
      <div class="mt-1 text-2xl font-semibold text-primary-700">1,102</div>
    </div>
  </div>
  <div class="rounded-2xl border border-gray-200 bg-white shadow-soft">
    <div class="p-4">
      <div class="text-xs text-gray-500">Bị khóa</div>
      <div class="mt-1 text-2xl font-semibold text-red-600">119</div>
    </div>
  </div>
</div>

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-soft">
  <div class="overflow-x-auto">
    <table class="min-w-full text-left text-sm">
      <thead class="bg-gray-50 text-xs uppercase text-gray-500">
        <tr>
          <th class="w-10 px-3 py-3">
            <input id="checkAll" type="checkbox" class="h-4 w-4 rounded border-gray-300" aria-label="Chọn tất cả">
          </th>
          <th class="px-3 py-3">#</th>
          <th class="px-3 py-3">Tên</th>
          <th class="px-3 py-3">Email</th>
          <th class="px-3 py-3">Vai trò</th>
          <th class="px-3 py-3">Trạng thái</th>
          <th class="px-3 py-3 text-right">Thao tác</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <tr class="hover:bg-gray-50/60">
          <td class="px-3 py-3">
            <input type="checkbox" class="row-check h-4 w-4 rounded border-gray-300" aria-label="Chọn hàng">
          </td>
          <td class="px-3 py-3">1</td>
          <td class="px-3 py-3">Nguyễn Văn A</td>
          <td class="px-3 py-3">a.nguyen@example.com</td>
          <td class="px-3 py-3">Admin</td>
          <td class="px-3 py-3">
            <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 ring-1 ring-green-200">Hoạt động</span>
          </td>
          <td class="px-3 py-3 text-right">
            <div class="inline-flex gap-2">
              <button type="button" class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium hover:bg-gray-200">Sửa</button>
              <button type="button" class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700">Xóa</button>
            </div>
          </td>
        </tr>

        <tr class="hover:bg-gray-50/60">
          <td class="px-3 py-3">
            <input type="checkbox" class="row-check h-4 w-4 rounded border-gray-300" aria-label="Chọn hàng">
          </td>
          <td class="px-3 py-3">2</td>
          <td class="px-3 py-3">Trần Thị B</td>
          <td class="px-3 py-3">b.tran@example.com</td>
          <td class="px-3 py-3">Editor</td>
          <td class="px-3 py-3">
            <span class="inline-flex items-center rounded-full bg-yellow-50 px-2 py-0.5 text-xs font-medium text-yellow-700 ring-1 ring-yellow-200">Chờ duyệt</span>
          </td>
          <td class="px-3 py-3 text-right">
            <div class="inline-flex gap-2">
              <button type="button" class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium hover:bg-gray-200">Sửa</button>
              <button type="button" class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700">Xóa</button>
            </div>
          </td>
        </tr>

        <tr class="hover:bg-gray-50/60">
          <td class="px-3 py-3">
            <input type="checkbox" class="row-check h-4 w-4 rounded border-gray-300" aria-label="Chọn hàng">
          </td>
          <td class="px-3 py-3">3</td>
          <td class="px-3 py-3">Lê Văn C</td>
          <td class="px-3 py-3">c.le@example.com</td>
          <td class="px-3 py-3">Viewer</td>
          <td class="px-3 py-3">
            <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700 ring-1 ring-red-200">Bị khóa</span>
          </td>
          <td class="px-3 py-3 text-right">
            <div class="inline-flex gap-2">
              <button type="button" class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium hover:bg-gray-200">Sửa</button>
              <button type="button" class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700">Xóa</button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
@endsection
