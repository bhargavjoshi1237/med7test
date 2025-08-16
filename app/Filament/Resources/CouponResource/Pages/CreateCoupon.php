<?php

namespace App\Filament\Resources\CouponResource\Pages;

use App\Filament\Resources\CouponResource;
use Lunar\Models\Discount;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCoupon extends CreateRecord
{
    protected static string $resource = CouponResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Extract discount-related data
        $createDiscount = $data['create_discount'] ?? false;
        $discountData = [];
        
        if ($createDiscount) {
            $discountData = [
                'name' => $data['name'],
                'handle' => $data['discount_handle'] ?? str()->slug($data['name']),
                'coupon' => $data['code'],
                'type' => $data['discount_type'] ?? 'coupon',
                'starts_at' => $data['starts_at'] ?? now(),
                'ends_at' => $data['expires_at'],
                // 'uses' => $data['discount_uses'] ?? 0,
                // 'max_uses' => $data['discount_max_uses'],
                // 'priority' => $data['discount_priority'] ?? 1,
                // 'stop' => $data['discount_stop'] ?? false,
                // 'restriction' => $data['discount_restriction'],
                // 'max_uses_per_user' => $data['discount_max_uses_per_user'],
                // 'data' => $data['discount_data'] ? json_decode($data['discount_data'], true) : null,
            ];
        }

        // Remove discount fields from coupon data
        $couponData = collect($data)->except([
            'create_discount',
            'discount_handle',
            'discount_type',
            'discount_uses',
            'discount_max_uses',
            'discount_priority',
            'discount_max_uses_per_user',
            'discount_stop',
            'discount_restriction',
            'discount_data'
        ])->toArray();

        // Create the coupon record
        $coupon = static::getModel()::create($couponData);

        // Create the discount record if requested
        if ($createDiscount) {
            try {
                Discount::create($discountData);
                
                // $this->notify('success', 'Coupon and discount created successfully!');
            } catch (\Exception $e) {
                // $this->notify('warning', 'Coupon created but discount creation failed: ' . $e->getMessage());
            }
        }

        return $coupon;
    }
}
