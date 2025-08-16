<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Affiliate Activity Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .summary {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-item .label {
            font-weight: bold;
            color: #666;
            font-size: 11px;
            text-transform: uppercase;
        }
        .summary-item .value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        td {
            font-size: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-percentage {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-flat {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Affiliate Activity Report</h1>
        <p><strong>Affiliate:</strong> {{ $affiliateName }}</p>
        <p><strong>Period:</strong> {{ $dateRange['start']->format('M d, Y') }} - {{ $dateRange['end']->format('M d, Y') }}</p>
        <p><strong>Generated:</strong> {{ now()->format('M d, Y H:i:s') }}</p>
    </div>

    <div class="summary">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Total Activities</div>
                <div class="value">{{ number_format($totalActivities) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Total Commission</div>
                <div class="value">${{ number_format($totalCommission, 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Average per Activity</div>
                <div class="value">${{ $totalActivities > 0 ? number_format($totalCommission / $totalActivities, 2) : '0.00' }}</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Affiliate</th>
               
                <th>SKU</th>
                <th>Buyer</th>
                <th>Product Price</th>
                <th>Commission Rate</th>
                <th>Commission Type</th>
                <th>Commission Amount</th>
                <th>Order Ref</th>
            </tr>
        </thead>
        <tbody>
            @foreach($activities as $activity)
            <tr>
                <td>{{ $activity->activity_date->format('M d, Y') }}</td>
                <td>{{ $activity->affiliate->name }}</td>
               
                <td>{{ $activity->productVariant->sku ?? 'N/A' }}</td>
                <td>{{ $activity->buyer->name ?? 'Guest' }}</td>
                <td class="text-right">${{ number_format($activity->product_price, 2) }}</td>
                <td class="text-right">{{ number_format($activity->commission_rate, 2) }}{{ $activity->commission_type === 'percentage' ? '%' : '' }}</td>
                <td class="text-center">
                    <span class="badge badge-{{ $activity->commission_type }}">
                        {{ $activity->commission_type === 'percentage' ? 'Percentage' : 'Flat' }}
                    </span>
                </td>
                <td class="text-right">${{ number_format($activity->commission_amount, 2) }}</td>
                <td>{{ $activity->order_reference ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This report was generated automatically by the Affiliate Management System.</p>
        <p>For questions or concerns, please contact the administrator.</p>
    </div>
</body>
</html>