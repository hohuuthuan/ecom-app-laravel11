<?php

namespace App\Services\Admin;

use App\Models\Brand;
use App\Helpers\PaginationHelper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class BrandService
{
  public function getList(array $filters = [])
  {
    $query = Brand::query();

    if (!empty($filters['keyword'])) {
      $kw = $filters['keyword'];
      $query->where(function ($q) use ($kw) {
        $q->where('name', 'LIKE', "%{$kw}%")
          ->orWhere('slug', 'LIKE', "%{$kw}%");
      });
    }
    if (!empty($filters['status'])) {
      $query->where('status', $filters['status']);
    }

    $perPage = (int)($filters['per_page'] ?? 10);
    $brands  = $query->orderBy('created_at', 'desc')->paginate($perPage);
    return PaginationHelper::appendQuery($brands);
  }

  // public function create(array $data, ?UploadedFile $image): bool
  // {
  //   $newPath = null;
  //   try {
  //     DB::beginTransaction();

  //     $data['slug'] = Str::slug($data['slug']);
  //     if ($image instanceof UploadedFile) {
  //       $filename = $image->hashName();
  //       $image->storeAs('categories', $filename, 'public');
  //       $data['image'] = $filename;

  //       $newPath = $image->store('brands', 'public');
  //       $data['image'] = $newPath;
  //     }

  //     Brand::query()->create([
  //       'name'        => $data['name'],
  //       'description' => $data['description'],
  //       'image'       => $data['image'] ?? NULL,
  //       'slug'        => $data['slug'],
  //       'status'      => $data['status'],
  //     ]);

  //     DB::commit();
  //     return true;
  //   } catch (Throwable $e) {
  //     DB::rollBack();
  //     if ($newPath && Storage::disk('public')->exists($newPath)) {
  //       Storage::disk('public')->delete($newPath);
  //     }
  //     Log::error('Brand create failed', ['msg'=>$e->getMessage()]);
  //     return false;
  //   }
  // }


  public function create(array $data, ?UploadedFile $image): bool
  {
    $savedFilename = null;
    try {
      DB::beginTransaction();

      $data['slug'] = Str::slug($data['slug']);
      if ($image instanceof UploadedFile) {
        $savedFilename = $image->hashName();
        $image->storeAs('brands', $savedFilename, 'public');
        $data['image'] = $savedFilename;
      }

      Brand::query()->create([
        'name'        => $data['name'],
        'description' => $data['description'],
        'image'       => $data['image'] ?? null,
        'slug'        => $data['slug'],
        'status'      => $data['status'],
      ]);

      DB::commit();
      return true;
    } catch (Throwable $e) {
      DB::rollBack();

      if ($savedFilename) {
        $path = 'brands/' . $savedFilename;
        if (Storage::disk('public')->exists($path)) {
          Storage::disk('public')->delete($path);
        }
      }

      Log::error('Brand create failed', ['msg' => $e->getMessage()]);
      return false;
    }
  }


  public function update(string $id, array $data, ?UploadedFile $image): bool
  {
    $oldPath = null;
    $newPath = null;
    try {
      DB::beginTransaction();

      $brand = Brand::query()->lockForUpdate()->findOrFail($id);

      $update = [
        'name'        => $data['name'],
        'description' => $data['description'] ?? null,
        'slug'        => Str::slug($data['slug']),
        'status'      => $data['status'],
      ];
      if ($image instanceof UploadedFile) {
        $oldPath = $brand->image;
        $newPath = $image->store('brands', 'public');
        $update['image'] = $newPath;
      }

      $brand->update($update);

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
      Log::error('Brand update failed', ['id' => $id, 'msg' => $e->getMessage()]);
      return false;
    }
  }

  public function delete(string $id): bool
  {
    try {
      DB::beginTransaction();
      $brand = Brand::query()->lockForUpdate()->findOrFail($id);
      $img   = $brand->image;
      $brand->delete();
      DB::commit();
      if ($img && Storage::disk('public')->exists($img)) {
        Storage::disk('public')->delete($img);
      }

      return true;
    } catch (Throwable $e) {
      DB::rollBack();
      Log::error('Brand delete failed', ['id' => $id, 'msg' => $e->getMessage()]);
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
