<div class="container">
    <h1>Payment Successful!</h1>
    <p>Your payment has been processed successfully.</p>
    
    @if(isset($payment_intent_id))
        <p>Payment ID: {{ $payment_intent_id }}</p>
        
        <h3>Refund Options</h3>
        
        <!-- Full Refund Form -->
        <form action="{{ route('payment.refund', ['paymentIntentId' => $payment_intent_id]) }}" method="POST" class="mb-3">
            @csrf
            <button type="submit" class="btn btn-warning">Full Refund</button>
        </form>
        
        <!-- Partial Refund Form -->
        <form action="{{ route('payment.refund', ['paymentIntentId' => $payment_intent_id]) }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="amount">Refund Amount ($)</label>
                <input type="number" step="0.01" min="0.01" max="10.00" class="form-control" id="amount" name="amount" placeholder="Enter amount to refund" required>
            </div>
            <button type="submit" class="btn btn-info mt-2">Process Partial Refund</button>
        </form>
    @endif

    @if(isset($payment_intent_id))
    <p>Payment ID: {{ $payment_intent_id }}</p>
    
    <a href="{{ route('payment.refund', ['paymentIntentId' => $payment_intent_id]) }}" class="btn btn-primary mb-3">View Refund History</a>
    
    <!-- Refund forms here -->
@endif
    
    <a href="/" class="mt-4 d-inline-block">Return to Home</a>
</div>