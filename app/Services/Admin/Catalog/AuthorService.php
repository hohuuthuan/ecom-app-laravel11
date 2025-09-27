<?php

namespace App\Services\Admin\Catalog;

use App\Models\Author;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Helpers\PaginationHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class AuthorService
{
  public function getList(array $filters = [])
  {
    $query = Author::query();

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
    $authors  = $query->orderBy('created_at', 'desc')->paginate($perPage);

    return PaginationHelper::appendQuery($authors);
  }

  public function create(array $data, UploadedFile $image): bool
  {
    $savedPath = null;
    try {
      $filename = $image->hashName();
      $savedPath = 'authors/' . $filename;
      $image->storeAs('authors', $filename, 'public');

      Author::query()->create([
        'name'        => $data['name'],
        'description' => $data['description'],
        'image'       => $filename,
        'slug'        => $data['slug'],
        'status'      => $data['status'],
      ]);

      return true;
    } catch (Throwable $e) {
      if ($savedPath && Storage::disk('public')->exists($savedPath)) {
        Storage::disk('public')->delete($savedPath);
      }
      Log::error('Author create failed', ['msg' => $e->getMessage()]);

      return false;
    }
  }

  public function update(string $id, array $data, ?UploadedFile $image = null): bool
  {
    $savedPath = null;
    $oldPath = null;
    try {
      $author = Author::query()->lockForUpdate()->findOrFail($id);

      $update = [
        'name'        => $data['name'],
        'slug'        => Str::slug($data['slug']),
        'description' => $data['description'],
        'status'      => $data['status'],
      ];

      if ($image instanceof UploadedFile) {
        $oldPath = $author->image ? ('authors/' . $author->image) : null;
        $newfileName = $image->hashName();
        $savedPath = 'authors/' . $newfileName;
        $image->storeAs('authors', $newfileName, 'public');
        $update['image'] = $newfileName;
      }

      $author->update($update);
      if ($oldPath && $savedPath && Storage::disk('public')->exists($oldPath)) {
        Storage::disk('public')->delete($oldPath);
      }

      return true;
    } catch (Throwable $e) {
      if ($savedPath && Storage::disk('public')->exists($savedPath)) {
        Storage::disk('public')->delete($savedPath);
      }
      Log::error('Author update failed', ['id' => $id, 'msg' => $e->getMessage()]);
      return false;
    }
  }

  public function delete(string $id): bool
  {
    try {
      $author = Author::query()->lockForUpdate()->findOrFail($id);
      $author->delete();

      return true;
    } catch (Throwable $e) {
      Log::error('Author delete failed', ['id' => $id, 'msg' => $e->getMessage()]);
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
