<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đang chuyển đến SePay...</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .box { text-align: center; padding: 2rem; }
        .spinner { width: 40px; height: 40px; border: 3px solid #334155; border-top-color: #22c55e; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 1rem; }
        @keyframes spin { to { transform: rotate(360deg); } }
        p { margin: 0; color: #94a3b8; }
    </style>
</head>
<body>
<div class="box">
    <div class="spinner"></div>
    <p>Đang chuyển đến trang thanh toán SePay...</p>
</div>
<form id="sepay-form" action="{{ $formAction }}" method="POST" style="display:none;">
    {{-- Thứ tự field theo tài liệu SePay --}}
    <input type="hidden" name="merchant" value="{{ $fields['merchant'] ?? '' }}">
    <input type="hidden" name="currency" value="{{ $fields['currency'] ?? '' }}">
    <input type="hidden" name="order_amount" value="{{ $fields['order_amount'] ?? '' }}">
    <input type="hidden" name="operation" value="{{ $fields['operation'] ?? '' }}">
    <input type="hidden" name="order_description" value="{{ $fields['order_description'] ?? '' }}">
    <input type="hidden" name="order_invoice_number" value="{{ $fields['order_invoice_number'] ?? '' }}">
    <input type="hidden" name="customer_id" value="{{ $fields['customer_id'] ?? '' }}">
    <input type="hidden" name="success_url" value="{{ $fields['success_url'] ?? '' }}">
    <input type="hidden" name="error_url" value="{{ $fields['error_url'] ?? '' }}">
    <input type="hidden" name="cancel_url" value="{{ $fields['cancel_url'] ?? '' }}">
    <input type="hidden" name="signature" value="{{ $fields['signature'] ?? '' }}">
</form>
<script>
document.getElementById('sepay-form').submit();
</script>
</body>
</html>
