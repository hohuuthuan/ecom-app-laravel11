<?php

namespace App\Services\Admin\Discount;

use App\Models\Discount;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DiscountService
{
  public function create(array $data): bool
  {
    try {
      $payload = $this->buildPayload($data);
      $payload['id'] = (string) Str::uuid();

      Discount::create($payload);

      return true;
    } catch (\Throwable $e) {
      Log::error('Create discount failed', [
        'error' => $e->getMessage(),
      ]);

      return false;
    }
  }

  public function update(string $id, array $data): bool
  {
    try {
      $discount = Discount::query()->find($id);

      if (!$discount) {
        return false;
      }

      $payload = $this->buildPayload($data);

      $discount->update($payload);

      return true;
    } catch (\Throwable $e) {
      Log::error('Update discount failed', [
        'id' => $id,
        'error' => $e->getMessage(),
      ]);

      return false;
    }
  }

  private function buildPayload(array $data): array
  {
    return [
      'code' => strtoupper($data['code']),
      'type' => $data['type'],
      'value' => (int) $data['value'],
      'min_order_value_vnd' => isset($data['min_order_value_vnd']) && $data['min_order_value_vnd'] !== ''
        ? (int) $data['min_order_value_vnd']
        : null,
      'usage_limit' => isset($data['usage_limit']) && $data['usage_limit'] !== ''
        ? (int) $data['usage_limit']
        : null,
      'per_user_limit' => isset($data['per_user_limit']) && $data['per_user_limit'] !== ''
        ? (int) $data['per_user_limit']
        : null,
      'status' => $data['status'] ?? 'ACTIVE',
      'start_date' => !empty($data['start_date'])
        ? $data['start_date']
        : null,
      'end_date' => !empty($data['end_date'])
        ? $data['end_date']
        : null,
    ];
  }
}
