{{-- resources/views/gcash-payment.blade.php --}}
{{-- This is a fallback page if payment processing fails --}}
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>GCash Payment - TOMECO</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  <style>
    body {
      font-family: 'Nunito', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background: #f3f4f6;
      margin: 0;
      padding: 20px;
    }
    .payment-container {
      max-width: 600px;
      margin: 50px auto;
      background: #fff;
      border-radius: 12px;
      padding: 30px;
      box-shadow: 0 4px 6px rgba(0,0,0,.1);
    }
    .gcash-logo {
      text-align: center;
      margin-bottom: 30px;
    }
    .gcash-logo h2 {
      color: #00cf35;
      font-size: 2rem;
      margin: 0;
    }
    .payment-details {
      background: #f9fafb;
      padding: 20px;
      border-radius: 8px;
      margin-bottom: 20px;
    }
    .payment-details h3 {
      margin: 0 0 15px 0;
      color: #111827;
    }
    .detail-row {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      border-bottom: 1px solid #e5e7eb;
    }
    .detail-row:last-child {
      border-bottom: none;
    }
    .detail-label {
      color: #6b7280;
      font-weight: 600;
    }
    .detail-value {
      color: #111827;
      font-weight: 700;
    }
    .amount {
      font-size: 1.5rem;
      color: #00cf35;
    }
    .info-message {
      background: #fef3c7;
      border: 1px solid #fbbf24;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 20px;
      color: #92400e;
    }
    .back-link {
      display: inline-block;
      margin-top: 20px;
      color: #6b7280;
      text-decoration: none;
    }
    .back-link:hover {
      color: #111827;
    }
  </style>
</head>
<body>
  <div class="payment-container">
    <div class="gcash-logo">
      <h2>GCash</h2>
      <p style="color: #6b7280; margin: 5px 0 0 0;">Pay your traffic violation ticket</p>
    </div>

    <div class="info-message">
      <strong>Note:</strong> You should have been redirected to PayMongo checkout. If you see this page, there may have been an issue with payment initialization.
    </div>

    <div class="payment-details">
      <h3>Payment Details</h3>
      <div class="detail-row">
        <span class="detail-label">Citation Number:</span>
        <span class="detail-value">{{ $payment_data['citation_number'] ?? 'N/A' }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Description:</span>
        <span class="detail-value">{{ $payment_data['description'] ?? 'Traffic Violation Ticket Payment' }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Amount:</span>
        <span class="detail-value amount">₱{{ number_format($payment_data['amount'] ?? 0, 2) }}</span>
      </div>
    </div>

    <a href="{{ route('violator.portal') }}" class="back-link">← Back to Portal</a>
  </div>
</body>
</html>

