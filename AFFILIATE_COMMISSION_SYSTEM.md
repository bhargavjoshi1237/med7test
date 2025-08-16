# Affiliate Commission System

## Overview
This system implements a comprehensive affiliate commission tracking and payout system with the following key features:

## Database Structure

### 1. affiliate_groups (Updated)
- Added `rate_type` (flat/percentage) 
- Added `rate` (numeric commission rate)

### 2. affiliate_activity (New)
- Tracks all affiliate sales activities
- Stores commission calculations per transaction
- Links affiliates to product variants and buyers

### 3. affiliate_product_rates (Existing)
- Defines specific commission rates per affiliate/product variant
- Overrides default affiliate rates when set

### 4. affiliate_minimum_thresholds (New)
- Stores minimum payout thresholds per affiliate
- One-to-one relationship with affiliates
- Prevents payouts below threshold amount

### 5. affiliates (Updated)
- Added `currency_id` - references lunar_currencies table
- Links affiliate commissions to specific currency

## Key Features

### 1. Commission Rate Hierarchy
1. **Product-specific rates** (affiliate_product_rates table) - highest priority
2. **Affiliate default rates** (affiliates table) - fallback
3. **Group rates** (affiliate_groups table) - for group-level defaults

### 2. Activity Tracking
- Every affiliate sale is recorded in `affiliate_activity`
- Includes product price, commission rate, type, and calculated amount
- Tracks buyer information and order references

### 3. Payout Calculation
- Calculates pending commissions since last cleared payout
- Shows activity summary and commission breakdown
- Auto-populates payout amounts based on calculations
- Supports multiple duration options (last paid, 1 month, 1 week, full)
- Checks minimum threshold requirements
- Multi-currency support through lunar_currencies integration

### 4. Minimum Threshold Management
- Prevents payouts below configured minimum amounts
- Per-affiliate threshold configuration
- Visual indicators when thresholds are not met

### 5. Currency Support
- Links affiliates to specific currencies from lunar_currencies
- Commission calculations respect affiliate's currency setting
- Multi-currency payout support

## Usage

### 1. Setting Up Commission Rates

#### For Affiliate Groups:
```php
$group = AffiliateGroup::create([
    'name' => 'Premium Partners',
    'rate_type' => 'percentage', // or 'flat'
    'rate' => 15.00 // 15% or $15 flat
]);
```

#### For Specific Products:
```php
AffiliateProductRate::create([
    'affiliate_id' => 1,
    'product_variant_id' => 123,
    'rate' => 20.00,
    'rate_type' => 'percentage'
]);
```

### 2. Recording Activity
```php
use App\Services\AffiliateCommissionService;

$service = new AffiliateCommissionService();

$activity = $service->recordActivity(
    affiliateId: 1,
    productVariantId: 123,
    productPrice: 99.99,
    buyerId: 456,
    orderReference: 'ORDER-12345'
);
```

### 3. Calculating Payouts
```php
$pendingCommission = $service->getPendingCommission(affiliateId: 1);

// Returns:
// - from_date: Last payout date or beginning of time
// - to_date: Current date
// - activities_count: Number of activities
// - total_commission: Total commission amount
// - activities: Collection of activities
// - last_payout: Last completed payout record
```

## Filament Resources

### 1. AffiliateGroupResource
- Updated form to include commission rate settings
- Shows commission type and rate in table view
- Reactive form fields with helpful text

### 2. AffiliatePayoutResource
- Enhanced CreateAffiliatePayout page with:
  - Affiliate selection with auto-calculation
  - Duration selection (last paid, 1 month, 1 week, full)
  - Minimum threshold checking and warnings
  - Last payout information display
  - Commission calculation summary
  - Auto-populated payout amounts
- Simplified EditAffiliatePayout page:
  - No referral_ids KeyValue component
  - Clean form focused on essential payout fields
  - Auto-sets paid_at when status changes to completed

### 3. AffiliateResource (Updated)
- Added currency selection field
- Added minimum threshold configuration
- Shows currency and threshold in table view
- Integrated with lunar_currencies table

### 4. Affiliate Dashboard (New)
- Comprehensive analytics dashboard with multiple widgets
- Real-time statistics and performance metrics
- Interactive charts and visualizations
- Top performers and recent activities tracking

## Console Commands

### Calculate Commissions
```bash
# For all active affiliates
php artisan affiliate:calculate-commissions

# For specific affiliate
php artisan affiliate:calculate-commissions 1
```

## Sample Data
Run the seeder to create sample data:
```bash
php artisan db:seed --class=AffiliateActivitySeeder
```

## Integration Points

### Order Processing
When an order is completed through an affiliate link:
```php
// In your order completion logic
$commissionService = app(AffiliateCommissionService::class);

foreach ($order->items as $item) {
    $commissionService->recordActivity(
        affiliateId: $affiliateId,
        productVariantId: $item->product_variant_id,
        productPrice: $item->unit_price,
        buyerId: $order->user_id,
        orderReference: $order->reference
    );
}
```

### Payout Processing
When marking a payout as completed:
```php
$payout = AffiliatePayout::find($payoutId);
$payout->update([
    'status' => 'completed',
    'paid_at' => now(),
    'transaction_id' => $externalTransactionId
]);
```

## Models and Relationships

### AffiliateActivity
- `affiliate()` - belongs to Affiliate
- `productVariant()` - belongs to ProductVariant
- `buyer()` - belongs to User

### Affiliate (Updated)
- `activities()` - has many AffiliateActivity

### AffiliateGroup (Updated)
- `calculateCommission($orderValue)` - helper method

## Services

### AffiliateCommissionService
- `recordActivity()` - Records new affiliate activity
- `calculateCommissionAmount()` - Calculates commission based on rate/type
- `getPendingCommission()` - Gets pending commission for affiliate
- `getCommissionRate()` - Gets applicable commission rate

## Dashboard Features

### Analytics Widgets
1. **Stats Overview** - Key metrics with month-over-month comparisons
2. **Commission Overview** - 12-month commission trend line chart
3. **Daily Activities** - 30-day activity bar chart
4. **Commission by Type** - Percentage vs flat rate doughnut chart
5. **Monthly Comparison** - Dual-axis chart showing commissions and activities
6. **Commission Rate Distribution** - Pie chart of affiliate rate ranges
7. **Payout Status Distribution** - Doughnut chart of payout statuses
8. **Top Affiliates** - Table widget showing best performers
9. **Recent Activities** - Latest affiliate activities table

### Key Metrics Displayed
- Total and active affiliate counts
- Monthly commission with growth percentage
- Monthly activities with growth percentage
- Pending and total payouts
- Performance comparisons and trends
- Rate distribution analysis
- Real-time activity monitoring

This system provides a complete solution for tracking affiliate activities, calculating commissions, and managing payouts with full audit trails, flexible rate structures, and comprehensive analytics dashboard.