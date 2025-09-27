<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AccountService;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\BulkUpdateAccountRequest;
use App\Http\Requests\Admin\UpdateAccountRequest;
use Illuminate\Http\RedirectResponse;
use Throwable;

class AccountController extends Controller
{
  protected AccountService $accountService;

  public function __construct(AccountService $accountService)
  {
    $this->accountService = $accountService;
  }

  public function index(Request $request)
  {
    $filters = $request->only(['keyword', 'role_id', 'status', 'per_page']);
    $users   = $this->accountService->getList($filters);

    $rolesForSelect = \App\Models\Role::query()
      ->select('id', 'name')
      ->orderBy('name')
      ->get();

    $rolesSummary = \App\Models\Role::query()
      ->select('name', 'description')
      ->withCount('users')
      ->orderBy('name')
      ->get();

    return view('admin.accounts.index', compact('users', 'rolesForSelect', 'rolesSummary'));
  }

  public function updateAccount(UpdateAccountRequest $request, string $id): RedirectResponse
  {
    try {
      $ok = $this->accountService->updateAccount($id, $request->validated());
      if (!$ok) {
        return back()->withInput()->with('toast_error', 'Cập nhật thất bại.');
      }
      return back()->with('toast_success', 'Cập nhật thành công.');
    } catch (Throwable $e) {
      return back()->withInput()->with('toast_error', 'Có lỗi xảy ra.');
    }
  }

  public function bulkUpdate(BulkUpdateAccountRequest $request)
  {
    $affected = $this->accountService->bulkUpdateStatus(
      $request->input('ids'),          // UUID[]
      $request->string('status')->toString() // 'ACTIVE' | 'BAN'
    );

    return redirect()
      ->route('admin.accounts.index')
      ->with('toast_success', "Đã cập nhật trạng thái '{$request->status}' cho {$affected} tài khoản.");
  }
}
