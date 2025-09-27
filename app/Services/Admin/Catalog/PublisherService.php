<?php

namespace App\Services\Admin\Catalog;

use App\Models\Publisher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Helpers\PaginationHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class PublisherService
{
  public function getList(array $filters = [])
  {
    $query = Publisher::query();

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

    $perPage = (int)($filters['per_page'] ?? 10);
    $publishers  = $query->orderBy('created_at', 'desc')->paginate($perPage);

    return PaginationHelper::appendQuery($publishers);
  }

  public function create(array $data, UploadedFile $image): bool
  {
    $savedPath = null;
    try {
      $fileName = $image->hashName();
      $savedPath = 'publishers/' . $fileName;
      $image->storeAs('publishers', $fileName, 'public');

      Publisher::query()->create([
        'name'        => $data['name'],
        'description' => $data['description'],
        'logo'        => $fileName,
        'slug'        => $data['slug'],
        'status'      => $data['status'],
      ]);

      return true;
    } catch (Throwable $e) {
      if ($savedPath && Storage::disk('public')->exists($savedPath)) {
        Storage::disk('public')->delete($savedPath);
      }
      Log::error('Publisher create failed', ['msg' => $e->getMessage()]);

      return false;
    }
  }

  public function update(string $id, array $data, ?UploadedFile $image = null): bool
  {
    $savedPath = null;
    $oldPath = null;
    try {
      $publisher = Publisher::query()->lockForUpdate()->findOrFail($id);

      $update = [
        'name'        => $data['name'],
        'slug'        => Str::slug($data['slug']),
        'description' => $data['description'] ?? null,
        'status'      => $data['status'],
      ];

      if ($image instanceof UploadedFile) {
        $oldPath = $publisher->logo ? ('publishers/' . $publisher->logo) : null;
        $fileName = $image->hashName();
        $savedPath = 'publishers/' . $fileName;
        $image->storeAs('publishers', $fileName, 'public');
        $update['logo'] = $fileName;
      }

      $publisher->update($update);
      if ($oldPath && $savedPath && Storage::disk('public')->exists($oldPath)) {
        Storage::disk('public')->delete($oldPath);
      }

      return true;
    } catch (Throwable $e) {
      if ($savedPath && Storage::disk('public')->exists($savedPath)) {
        Storage::disk('public')->delete($savedPath);
      }
      Log::error('Publisher update failed', ['id' => $id, 'msg' => $e->getMessage()]);
      return false;
    }
  }

  public function delete(string $id): bool
  {
    try {
      $publisher = Publisher::query()->lockForUpdate()->findOrFail($id);
      $publisher->delete();

      return true;
    } catch (Throwable $e) {
      Log::error('Publisher delete failed', ['id' => $id, 'msg' => $e->getMessage()]);
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
