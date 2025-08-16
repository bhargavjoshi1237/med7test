<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Affiliate Reports</title>
    {{-- Add your primary CSS framework here, e.g., Bootstrap or Tailwind CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .nav-tabs .nav-link.active {
            font-weight: bold;
        }
        .status-paid { background-color: #d4edda; color: #155724; }
        .status-unpaid { background-color: #fff3cd; color: #856404; }
        .status-pending { background-color: #cce5ff; color: #004085; }
        .status-rejected { background-color: #f8d7da; color: #721c24; }
        .filter-card { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <h2>Affiliate Reports</h2>

        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.reports.referrals') ? 'active' : '' }}" href="{{ route('admin.reports.referrals') }}">Referrals</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.reports.affiliates') ? 'active' : '' }}" href="{{ route('admin.reports.affiliates') }}">Affiliates</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.reports.sales') ? 'active' : '' }}" href="{{ route('admin.reports.sales') }}">Sales</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.reports.payouts') ? 'active' : '' }}" href="{{ route('admin.reports.payouts') }}">Payouts</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.reports.visits') ? 'active' : '' }}" href="{{ route('admin.reports.visits') }}">Visits</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.reports.campaigns') ? 'active' : '' }}" href="{{ route('admin.reports.campaigns') }}">Campaigns</a>
            </li>
        </ul>

        <div class="card shadow-sm mb-4">
            <div class="card-body filter-card">
                @yield('filters')
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>