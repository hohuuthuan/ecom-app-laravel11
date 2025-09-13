<?php

namespace App\Services\Admin;

use App\Models\Category;
use App\Helpers\PaginationHelper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class CategoryService
{
  public function getList(array $filters = [])
  {
    $query = Category::query();
    if (!empty($filters['keyword'])) {
      $kw = $filters['keyword'];
      $query->where(function ($q) use ($kw) {
        $q->where('name','LIKE',"%{$kw}%")
          ->orWhere('slug','LIKE',"%{$kw}%");
      });
    }
    if (!empty($filters['status'])) {
      $query->where('status', $filters['status']);
    }

    $perPage   = (int)($filters['per_page'] ?? 10);
    $categories= $query->orderBy('created_at','desc')->paginate($perPage);

    return PaginationHelper::appendQuery($categories);
  }

  public function listParents()
  {
    return Category::query()
      ->select('id','name')
      ->orderBy('name','asc')
      ->get();
  }

  public function create(array $data, ?UploadedFile $image): bool
  {
    $newPath = null;
    try {
      DB::beginTransaction();

      $data['slug'] = Str::slug($data['slug']);
      if ($image instanceof UploadedFile) {
        $newPath = $image->store('categories', 'public');
        $data['image'] = $newPath;
      }

      Category::query()->create([
        'name'        => $data['name'],
        'description' => $data['description'],
        'image'       => $data['image'],
        'slug'        => $data['slug'],
        'status'      => $data['status'],
      ]);

      DB::commit();
      return true;
    } catch (Throwable $e) {
      DB::rollBack();
      if ($newPath && Storage::disk('public')->exists($newPath)) {
        Storage::disk('public')->delete($newPath);
      }
      Log::error('Category create failed', ['msg'=>$e->getMessage()]);

      return false;
    }
  }

  public function update(string $id, array $data, ?UploadedFile $image): bool
  {
    $oldPath = null;
    $newPath = null;
    try {
      DB::beginTransaction();

      $category = Category::query()->lockForUpdate()->findOrFail($id);

      $update = [
        'name'        => $data['name'],
        'description' => $data['description'],
        'slug'        => Str::slug($data['slug']),
        'status'      => $data['status'],
      ];
      if ($image instanceof UploadedFile) {
        $oldPath = $category->image;
        $newPath = $image->store('categories', 'public');
        $update['image'] = $newPath;
      }

      $category->update($update);

      DB::commit();
      if ($oldPath && $oldPath !== $newPath && Storage::disk('public')->exists($oldPath)) {
        Storage::disk('public')->delete($oldPath);
      }

      return true;
    } catch (Throwable $e) {
      DB::rollBack();
      if ($newPath && Storage::disk('public')->exists($newPath)) {
        Storage::disk('public')->delete($newPath);
      }
      Log::error('Category update failed', ['id'=>$id,'msg'=>$e->getMessage()]);

      return false;
    }
  }

  public function delete(string $id): bool
  {
    try {
      DB::beginTransaction();
      $cat = Category::query()->lockForUpdate()->findOrFail($id);
      $img = $cat->image;
      $cat->delete();

      DB::commit();
      if ($img && Storage::disk('public')->exists($img)) {
        Storage::disk('public')->delete($img);
      }
      
      return true;
    } catch (Throwable $e) {
      DB::rollBack();
      Log::error('Category delete failed', ['id'=>$id,'msg'=>$e->getMessage()]);

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
