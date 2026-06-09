<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.8; color: #333; margin: 20px; }
        .section-title { font-weight: bold; margin-top: 24px; margin-bottom: 12px; font-size: 14px; }
        .section { margin-bottom: 8px; }
        .section p { margin: 6px 0; }
        .section .label { font-weight: bold; }
        hr { margin: 24px 0; border: none; border-top: 1px solid #ccc; }
        .list-item { margin: 8px 0 8px 16px; }
    </style>
</head>
<body>

<p>Dear {{ $booking->full_name }},</p>

<p>Thank you for choosing our cruise services. We are pleased to confirm your reservation with the following details:</p>

<hr>

<p class="section-title">BOOKING INFORMATION</p>
<div class="section">
    <p>Tour: {{ $booking->itinerary_name ?? 'N/A' }}</p>
    <p>Cruise: {{ $booking->cruise_name ?? 'N/A' }}</p>
    <p>Departure Date: {{ $booking->departure_date ? $booking->departure_date->format('m/d/Y') : 'N/A' }}</p>
    <p>Number of Guests: {{ $booking->guests_total ?? 0 }} people</p>
</div>

<hr>

<p class="section-title">CABIN INFORMATION</p>
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
    <p>No cabins.</p>
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

<p class="section-title">ESTIMATED TOTAL</p>
<div class="section">
    @php
        $estimatedTotal = \Modules\BackEnd\Helpers\Utilities::formatStoredAmount($booking->total_amount, $booking->currency, false);
    @endphp
    <p>Estimated Total: {{ strtoupper($booking->currency) }} {{ $estimatedTotal }}</p>
</div>

<hr>

<p class="section-title">PAYMENT INFORMATION</p>
<div class="section">
    @php
        $totalAmount = \Modules\BackEnd\Helpers\Utilities::formatStoredAmount($booking->total_amount, $booking->currency, false);
    @endphp
    <p>Total Amount: {{ strtoupper($booking->currency) }} {{ $totalAmount }}</p>
    <p>Payment Methods:</p>
    <p class="list-item">• {{ $paymentMethodLabel }}</p>
</div>

<hr>

<p class="section-title">CANCELLATION POLICY</p>
<div class="section">
    <p class="list-item">• Cancellation 7 days before departure: Free</p>
    <p class="list-item">• Cancellation 3–7 days before departure: 50% charge</p>
    <p class="list-item">• Cancellation under 3 days before departure: 100% charge</p>
</div>

<p style="margin-top: 20px;">If you have any special requests or require further assistance, please do not hesitate to contact us.</p>

<p>We look forward to welcoming you on board and providing you with an unforgettable journey.</p>

<hr>

<p>Best regards,</p>
<p style="margin-top: 12px;">Customer Service Team<br>
{{ $companyName }}<br>
Hotline: {{ $hotline }}<br>
Email: {{ $supportEmail }}<br>
Website: {{ $website }}</p>

</body>
</html>
