<div class="container">
    <h1>Refund History</h1>
    
    <div class="card mb-4">
        <div class="card-header">Payment Details</div>
        <div class="card-body">
            <p><strong>Payment ID:</strong> {{ $payment->payment_intent_id }}</p>
            <p><strong>Original Amount:</strong> ${{ number_format($payment->amount, 2) }}</p>
            <p><strong>Refunded Amount:</strong> ${{ number_format($payment->refunded_amount, 2) }}</p>
            <p><strong>Status:</strong> {{ ucfirst($payment->status) }}</p>
        </div>
    </div>
    
    <h2>Refunds</h2>
    
    @if(count($refunds) > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Refund ID</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($refunds as $refund)
                    <tr>
                        <td>{{ $refund->id }}</td>
                        <td>${{ number_format($refund->amount / 100, 2) }}</td>
                        <td>{{ $refund->status }}</td>
                        <td>{{ date('Y-m-d H:i:s', $refund->created) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No refunds have been processed for this payment.</p>
    @endif
    
    <a href="{{ route('payment.success', ['session_id' => session('session_id')]) }}" class="btn btn-secondary">Back to Payment</a>
</div>