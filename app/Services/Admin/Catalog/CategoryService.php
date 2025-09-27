<?php

namespace App\Services\Admin\Catalog;

use App\Models\Category;
use App\Helpers\PaginationHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class CategoryService
{
  public function getList(array $filters = [])
  {
    $query = Category::query();
    if (!empty($filters['keyword'])) {
      $keyword = $filters['keyword'];
      $query->where(function ($query) use ($keyword) {
        $query->where('name', 'LIKE', "%{$keyword}%")
          ->orWhere('slug', 'LIKE', "%{$keyword}%");
      });
    }
    if (!empty($filters['status'])) {
      $query->where('status', $filters['status']);
    }

    $perPage    = (int)($filters['per_page'] ?? 10);
    $categories = $query->orderBy('name', 'asc')->paginate($perPage);

    return PaginationHelper::appendQuery($categories);
  }

  public function listParents()
  {
    return Category::query()
      ->select('id', 'name')
      ->orderBy('name', 'asc')
      ->get();
  }

  public function create(array $data): bool
  {
    try {
      Category::query()->create([
        'name'        => $data['name'],
        'slug'        => $data['slug'],
        'description' => $data['description'],
        'status'      => $data['status'],
      ]);

      return true;
    } catch (Throwable $e) {
      Log::error('Category create failed', ['msg' => $e->getMessage()]);
      return false;
    }
  }

  public function update(string $id, array $data): bool
  {
    try {
      $category = Category::query()->lockForUpdate()->findOrFail($id);

      $update = [
        'name'        => $data['name'],
        'description' => $data['description'],
        'slug'        => Str::slug($data['slug']),
        'status'      => $data['status'],
      ];

      $category->update($update);

      return true;
    } catch (Throwable $e) {
      Log::error('Category update failed', ['id' => $id, 'msg' => $e->getMessage()]);
      return false;
    }
  }

  public function delete(string $id): bool
  {
    try {
      $cat = Category::query()->lockForUpdate()->findOrFail($id);
      $cat->delete();

      return true;
    } catch (Throwable $e) {
      Log::error('Category delete failed', ['id' => $id, 'msg' => $e->getMessage()]);
      return false;
    }
  }

  public function bulkDelete(array $ids): int
  {
    $count = 0;
    foreach ($ids as $id) {
      $count += $this->delete($id) ? 1 : 0;
    }
    return $count;
  }
}
