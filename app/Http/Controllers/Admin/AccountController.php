<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AccountService;
use App\Models\Role;
use Illuminate\Http\Request;

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
}
