# Affiliate Product Rates System

This system allows you to set specific commission rates for affiliates on individual products or product variants, overriding their default commission rates.

## How It Works

When an order is placed through an affiliate's referral:

1. **Variant-Specific Rate**: If a rate is set for the specific product variant, use that rate
2. **Product-Specific Rate**: If no variant rate exists, check for a product-level rate
3. **Default Rate**: If no specific rates exist, fall back to the affiliate's default rate

## Setting Up Product Rates

### Via Admin Panel

1. Navigate to **Affiliates** in the admin panel
2. Edit an affiliate
3. Go to the **Product Rates** tab
4. Click **Add Product Rate**
5. Select either:
   - A base product (applies to all variants of that product)
   - A specific variant (applies only to that variant)
6. Set the commission rate and type (percentage or flat amount)

### Rate Types

- **Percentage**: Commission calculated as a percentage of the sale amount
  - Example: 10% rate on $100 sale = $10 commission
- **Flat Amount**: Fixed commission amount regardless of sale value
  - Example: $5 flat rate = $5 commission on any sale

## Database Structure

### affiliate_product_rates Table

- `affiliate_id`: Links to the affiliate
- `product_id`: String field that can contain:
  - `product_123` for base product rates
  - `variant_456` for specific variant rates
- `rate`: The commission rate value
- `rate_type`: Either 'percentage' or 'flat'

## Usage Examples

### Setting Rates

```php
use App\Models\Affiliate;
use App\Models\AffiliateProductRate;

$affiliate = Affiliate::find(1);

// Set rate for entire product
AffiliateProductRate::create([
    'affiliate_id' => $affiliate->id,
    'product_id' => 'product_123',
    'rate' => 15.00,
    'rate_type' => 'percentage'
]);

// Set rate for specific variant
AffiliateProductRate::create([
    'affiliate_id' => $affiliate->id,
    'product_id' => 'variant_456',
    'rate' => 5.00,
    'rate_type' => 'flat'
]);
```

### Getting Commission Rates

```php
$affiliate = Affiliate::find(1);

// Get rate for specific variant
$rateInfo = $affiliate->getCommissionRate($productId = 123, $variantId = 456);
// Returns: ['rate' => 15.00, 'rate_type' => 'percentage', 'source' => 'variant_specific']

// Calculate commission
$commission = $affiliate->calculateCommission($orderValue = 100, $productId = 123, $variantId = 456);
// Returns: 15.00 (for 15% of $100)
```

### Processing Order Commissions

```php
use App\Services\AffiliateCommissionService;

$service = new AffiliateCommissionService();
$referral = $service->processOrderCommissions($order, $affiliate);
```

## Testing

Run the test command to see how commissions are calculated:

```bash
php artisan affiliate:test-commissions
```

This will show:
- Current affiliates and their default rates
- Products and variants in the system
- Commission calculations for each product/variant combination

## Key Features

1. **Hierarchical Rate Resolution**: Variant rates override product rates, which override default rates
2. **Flexible Product Selection**: Support for both base products and specific variants
3. **Rate Type Support**: Both percentage and flat rate commissions
4. **Validation**: Prevents duplicate rates for the same affiliate/product combination
5. **Commission Preview**: Shows example calculations in the admin interface
6. **Detailed Tracking**: Records commission source (variant_specific, product_specific, or default)

## Integration Points

### Order Processing
When processing orders, the system automatically:
- Identifies the affiliate (via tracking, coupons, etc.)
- Calculates appropriate commission rates for each order line
- Creates affiliate referral records with detailed commission breakdowns

### Reporting
Commission records include:
- Source of the rate used (variant/product/default)
- Detailed breakdown per order line
- Product and variant information
- Rate calculations and totals

This system provides maximum flexibility for affiliate programs while maintaining clear audit trails and easy management through the admin interface.