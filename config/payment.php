<?php

return [
    'default_status' => 'pending',

    'statuses' => [
        'pending' => 'Đang chờ xác nhận từ cổng thanh toán (IPN/webhook).',
        'succeeded' => 'Thanh toán thành công.',
        'failed' => 'Thanh toán thất bại.',
        'canceled' => 'Giao dịch đã hủy.',
    ],

    'error_messages' => [
        'user_cancelled' => 'Bạn đã hủy giao dịch.',
        'paypal_capture_declined' => 'Ngân hàng hoặc PayPal từ chối thanh toán. Vui lòng thử thẻ khác hoặc liên hệ ngân hàng.',
        'paypal_capture_failed' => 'Thanh toán thất bại. Vui lòng thử lại hoặc dùng phương thức khác.',
        'paypal_order_voided' => 'Giao dịch đã bị hủy bởi PayPal.',
        'paypal_order_created' => 'Bạn chưa hoàn tất thanh toán trên trang PayPal.',
        'paypal_order_saved' => 'Bạn chưa hoàn tất thanh toán trên trang PayPal.',
        'paypal_payment_capture_denied' => 'Ngân hàng hoặc PayPal từ chối thanh toán.',
        'paypal_checkout_payment-approval_reversed' => 'PayPal đã hủy xác nhận thanh toán. Vui lòng thử lại hoặc dùng thẻ khác.',
        'paypal_capture_error' => 'Không thể xác nhận thanh toán. Vui lòng thử lại.',
        'paypal_capture_malformed_request_json' => 'Lỗi định dạng yêu cầu thanh toán. Vui lòng thử lại.',
        'paypal_capture_on_hold' => 'Thanh toán đang bị giữ bởi PayPal. Số dư sẽ khả dụng khi được giải phóng.',
    ],

    'methods' => ['stripe', 'paypal', 'sepay'],

    'gateways' => [
        'stripe' => \App\Payments\Gateways\StripePaymentGateway::class,
        'paypal' => \App\Payments\Gateways\PaypalPaymentGateway::class,
        'sepay' => \App\Payments\Gateways\SePayPaymentGateway::class,
    ],
];

