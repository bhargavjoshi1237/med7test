# Affiliate Coupon Processing Update

## Overview
Updated the affiliate coupon processing logic in `CheckoutNewPage.php` to use cart-based coupon detection instead of relying on the order's `discount_breakdown` field.

## Key Changes Made

### 1. Data Source Change
- **Before**: Used `order->discount_breakdown` field to find applied coupons
- **After**: Uses `cart->coupon_code` field directly from the cart

### 2. Simplified Logic Flow
- **Before**: 
  1. Parse JSON discount breakdown
  2. Loop through discount items
  3. Find discount by ID
  4. Get coupon from discount
  5. Check if coupon is affiliate coupon

- **After**:
  1. Check if cart has coupon_code
  2. Directly query coupons table with coupon code
  3. Verify if coupon belongs to an affiliate

### 3. Enhanced Commission Calculation
- **Before**: Manual commission calculation using coupon amount/type
- **After**: Uses `AffiliateCommissionService` for proper rate calculation
- **New Feature**: Supports product-specific commission rates from `affiliate_product_rates` table

### 4. Improved Rate Resolution Hierarchy
1. **Product-specific rates** (highest priority) - from `affiliate_product_rates` table
2. **Affiliate default rates** (fallback) - from `affiliates` table

### 5. Better Error Handling & Logging
- Added comprehensive logging at each step
- Enhanced error messages with context
- Added rate source tracking (product_specific vs affiliate_default)
- Better exception handling with stack traces

## Technical Implementation

### Updated Function Signature
```php
private function processAffiliateCoupons($orderId, $cart, $orderReference)
```

### Key Logic Changes
```php
// OLD: Parse discount breakdown
$discountBreakdown = json_decode($order->discount_breakdown, true);

// NEW: Direct coupon code check
if (empty($cart->coupon_code)) {
    return false;
}
```

### Service Integration
```php
// NEW: Use commission service for proper calculation
$commissionService = app(AffiliateCommissionService::class);
$rateInfo = $commissionService->getCommissionRate($affiliateId, $productVariantId);
```

### Product-Specific Rate Support
```php
// NEW: Check for product-specific rates
$productRate = DB::table('affiliate_product_rates')
    ->where('affiliate_id', $couponRow->affiliate_id)
    ->where('product_variant_id', $cartLine->purchasable_id)
    ->first();
```

## Benefits

### 1. Reliability
- No longer dependent on complex discount breakdown parsing
- Direct access to coupon information
- Reduced chance of data parsing errors

### 2. Performance
- Fewer database queries
- Simplified logic flow
- Direct table lookups instead of JSON parsing

### 3. Flexibility
- Support for product-specific commission rates
- Proper rate hierarchy resolution
- Better integration with existing affiliate system

### 4. Maintainability
- Uses established service layer (`AffiliateCommissionService`)
- Better separation of concerns
- Comprehensive logging for debugging

### 5. Accuracy
- Proper commission calculation using service methods
- Support for different rate types (percentage/flat)
- Product variant specific rate matching

## Database Tables Involved

### Primary Tables
- `lunar_carts` - Source of coupon_code
- `coupons` - Affiliate coupon lookup
- `affiliate_product_rates` - Product-specific commission rates
- `affiliate_activity` - Commission tracking
- `affiliatecommissionlog` - Audit trail

### Rate Resolution Flow
1. Check `affiliate_product_rates` for specific product variant rate
2. Fall back to `affiliates` table for default rate
3. Use `AffiliateCommissionService` for calculation

## Logging Enhancements

### New Log Fields
- `cart_coupon_code` - Coupon code from cart
- `rate_source` - Whether rate came from product_specific or affiliate_default
- `activity_id` - Reference to created affiliate activity record
- `quantity` - Product quantity for better tracking

### Enhanced Error Logging
- Stack traces for exceptions
- Context-rich error messages
- Step-by-step processing logs

## Testing

Created `test_affiliate_coupon_update.php` to verify:
- Cart coupon code retrieval
- AffiliateCommissionService integration
- Commission calculation accuracy
- Database structure compatibility

## Migration Notes

### No Database Changes Required
- Uses existing cart `coupon_code` field
- Leverages existing `affiliate_product_rates` table
- Compatible with current affiliate system

### Backward Compatibility
- Maintains same function signature
- Returns same boolean result
- Preserves existing logging patterns

## Future Enhancements

### Potential Improvements
1. Cache frequently accessed affiliate rates
2. Batch process multiple cart lines
3. Add commission preview functionality
4. Support for tiered commission rates

### Monitoring Recommendations
1. Monitor commission calculation accuracy
2. Track rate source distribution
3. Watch for any coupon processing errors
4. Verify affiliate activity creation

This update significantly improves the reliability and accuracy of affiliate coupon processing while maintaining compatibility with the existing system.