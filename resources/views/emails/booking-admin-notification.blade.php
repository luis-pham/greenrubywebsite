<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Booking Notification</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.8; color: #333; margin: 20px; }
        .section-title { font-weight: bold; margin-top: 24px; margin-bottom: 12px; font-size: 14px; }
        .section { margin-bottom: 8px; }
        .section p { margin: 6px 0; }
        .section .label { font-weight: bold; }
        hr { margin: 24px 0; border: none; border-top: 1px solid #ccc; }
        .list-item { margin: 8px 0 8px 0; display: flex; align-items: flex-start; }
        .checkbox-box {
            width: 12px;
            height: 12px;
            border: 1px solid #333;
            display: inline-block;
            margin-right: 8px;
            margin-top: 4px;
            box-sizing: border-box;
        }
        .list-item-text { flex: 1; }
    </style>
</head>
<body>

<p>Dear System Administrator,</p>

<p>A new cruise reservation has been successfully created in the system.<br>
Please review the details below for verification and further processing.</p>

<hr>

<p class="section-title">BOOKING SUMMARY</p>
<div class="section">
    <p>Booking ID: {{ $booking->code }}</p>
    <p>Booking Date: {{ $booking->created_at->format('m/d/Y H:i:s') }}</p>
    <p>Status: {{ ucfirst($booking->status) }}</p>
</div>

<hr>

<p class="section-title">CUSTOMER INFORMATION</p>
<div class="section">
    <p>Customer Name: {{ $booking->full_name }}</p>
    <p>Email: {{ $booking->email ?? 'N/A' }}</p>
    <p>Phone Number: {{ $booking->phone ?? 'N/A' }}</p>
</div>

<hr>

<p class="section-title">CRUISE INFORMATION</p>
<div class="section">
    <p>Tour: {{ $booking->itinerary_name ?? 'N/A' }}</p>
    <p>Cruise: {{ $booking->cruise_name ?? 'N/A' }}</p>
    <p>Departure Date: {{ $booking->departure_date ? $booking->departure_date->format('m/d/Y') : 'N/A' }}</p>
    <p>Number of Guests: {{ $booking->guests_total ?? 0 }} people</p>
</div>

<hr>

<p class="section-title">CABIN DETAILS</p>
<div class="section">
@if($booking->cabins && $booking->cabins->count() > 0)
@foreach($booking->cabins as $cabin)
    <p><span class="label">Cabin Type:</span> {{ $cabin->cabin_name }}</p>
    <p>Guests: @php
        $parts = [];
        if ($cabin->adults > 0) $parts[] = $cabin->adults . ' Adult' . ($cabin->adults > 1 ? 's' : '');
        if ($cabin->children_6_12 > 0) $parts[] = $cabin->children_6_12 . ' Child (6-12y)';
        if ($cabin->children_2_5 > 0) $parts[] = $cabin->children_2_5 . ' Child (2-5y)';
        if ($cabin->infants > 0) $parts[] = $cabin->infants . ' Infant';
    @endphp {{ implode(', ', $parts) ?: 'N/A' }}</p>
    @php
        $cabinPrice = \Modules\BackEnd\Helpers\Utilities::formatStoredAmount($cabin->total_price, $booking->currency, false);
    @endphp
    <p>Cabin Price: {{ strtoupper($booking->currency) }} {{ $cabinPrice }}</p>
    <p style="margin-bottom: 16px;"></p>
@endforeach
@else
    <p>None.</p>
@endif
</div>

<hr>

<p class="section-title">ADDITIONAL SERVICES</p>
<div class="section">
@if($booking->amenities && $booking->amenities->count() > 0)
@foreach($booking->amenities as $amenity)
    <p><span class="label">{{ $amenity->amenity_name }}</span></p>
    @php
        $amenityQty = max((int) ($amenity->quantity ?? 1), 1);
        $amenityLineTotal = ($amenity->unit_price ?? 0) * $amenityQty;
        $amenityPrice = \Modules\BackEnd\Helpers\Utilities::formatStoredAmount($amenityLineTotal, $booking->currency, false);
    @endphp
    <p>Quantity: {{ $amenityQty }}</p>
    @php
        // $amenityPrice already computed above
    @endphp
    <p>Price: {{ strtoupper($booking->currency) }} {{ $amenityPrice }}</p>
    <p style="margin-bottom: 12px;"></p>
@endforeach
@else
    <p>None.</p>
@endif
</div>

<hr>

    <p class="section-title">PAYMENT INFORMATION</p>
<div class="section">
    @php
        $totalAmount = \Modules\BackEnd\Helpers\Utilities::formatStoredAmount($booking->total_amount, $booking->currency, false);
    @endphp
    <p>Total Amount: {{ strtoupper($booking->currency) }} {{ $totalAmount }}</p>
    <p>Payment Method: {{ $paymentMethodLabel }}</p>
    <p>Payment Status: {{ ucfirst($booking->payment->status ?? 'Pending') }}</p>
    <p>Payment Deadline: {{ $booking->payment && $booking->payment->completed_at ? $booking->payment->completed_at->format('m/d/Y H:i') : 'N/A' }}</p>
    <p>Transaction ID: {{ $booking->payment && $booking->payment->provider_tx_id ? $booking->payment->provider_tx_id : 'N/A' }}</p>
</div>

<hr>

<p class="section-title">ADMIN ACTION REQUIRED</p>
<div class="section">
    <p class="list-item">
        <span class="checkbox-box"></span>
        <span class="list-item-text">Verify customer information</span>
    </p>
    <p class="list-item">
        <span class="checkbox-box"></span>
        <span class="list-item-text">Confirm cabin availability</span>
    </p>
    <p class="list-item">
        <span class="checkbox-box"></span>
        <span class="list-item-text">Validate payment status</span>
    </p>
    <p class="list-item">
        <span class="checkbox-box"></span>
        <span class="list-item-text">Update booking status</span>
    </p>
    <p class="list-item">
        <span class="checkbox-box"></span>
        <span class="list-item-text">Send confirmation email to customer</span>
    </p>
</div>

<p style="margin-top: 20px;">Please log in to the management system to process this booking accordingly.</p>

<p><span class="label">Admin Portal:</span> {{ $adminPortalUrl }}</p>

<p style="margin-top: 16px;">For internal support, please contact the technical team.</p>

<hr>

<p>Best regards,</p>
<p style="margin-top: 12px;">Booking System Notification<br>
{{ $companyName }}<br>
System Auto-Mailer<br>
This is an automated message. Please do not reply.</p>

</body>
</html>
