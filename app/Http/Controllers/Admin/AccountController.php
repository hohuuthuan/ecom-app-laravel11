<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AccountService;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\BulkUpdateAccountRequest;

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
