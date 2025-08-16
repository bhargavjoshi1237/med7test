<?php

namespace App\Filament\Resources\CouponResource\Pages;

use App\Filament\Resources\CouponResource;
use Lunar\Models\Discount;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditCoupon extends EditRecord
{
    protected static string $resource = CouponResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load existing discount data if it exists
        $discount = Discount::where('coupon', $this->record->code)->first();
        
        if ($discount) {
            $data['create_discount'] = true;
            $data['discount_handle'] = $discount->handle;
            $data['discount_type'] = $discount->type;
            $data['discount_uses'] = $discount->uses;
            $data['discount_max_uses'] = $discount->max_uses;
            $data['discount_priority'] = $discount->priority;
            $data['discount_max_uses_per_user'] = $discount->max_uses_per_user;
            $data['discount_stop'] = $discount->stop;
            $data['discount_restriction'] = $discount->restriction;
            $data['discount_data'] = $discount->data ? json_encode($discount->data) : null;
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Extract discount-related data
        $createDiscount = $data['create_discount'] ?? false;
        $existingDiscount = Discount::where('coupon', $record->code)->first();
        
        if ($createDiscount) {
            $discountData = [
                'name' => $data['name'],
                'handle' => $data['discount_handle'] ?? str()->slug($data['name']),
                'coupon' => $data['code'],
                'type' => $data['discount_type'] ?? 'coupon',
                'starts_at' => $data['starts_at'] ?? now(),
                'ends_at' => $data['expires_at'],
                'uses' => $data['discount_uses'] ?? 0,
                'max_uses' => $data['discount_max_uses'] ?? null,
                'priority' => $data['discount_priority'] ?? 1,
                'stop' => $data['discount_stop'] ?? false,
                'restriction' => $data['discount_restriction'] ?? null,
                'max_uses_per_user' => $data['discount_max_uses_per_user'] ?? null,
                'data' => $data['discount_data'] ? json_decode($data['discount_data'], true) : null ?? [],
            ];

            try {
                if ($existingDiscount) {
                    $existingDiscount->update($discountData);
                } else {
                    Discount::create($discountData);
                }
                
                $this->notify('success', 'Coupon and discount updated successfully!');
            } catch (\Exception $e) {
                $this->notify('warning', 'Coupon updated but discount update failed: ' . $e->getMessage());
            }
        } elseif ($existingDiscount) {
            // If discount checkbox is unchecked but discount exists, delete it
            try {
                $existingDiscount->delete();
                $this->notify('success', 'Coupon updated and associated discount removed!');
            } catch (\Exception $e) {
                $this->notify('warning', 'Coupon updated but discount removal failed: ' . $e->getMessage());
            }
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

        // Update the coupon record
        $record->update($couponData);

        return $record;
    }
}
