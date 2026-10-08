Your Quotation - Quote Reference: #{{ $quote->id }}

Dear {{ $quote->first_name }} {{ $quote->last_name }},

Thank you for your interest in our products. We're pleased to provide you with the following quotation:

@if($customMessage)
Message from us:
{{ $customMessage }}
@endif

Product Details:
- Product: {{ $quote->product_title }}
@if($quote->selected_variant)
- Variant: {{ $quote->selected_variant }}
@endif
- Quantity: {{ $quote->quantity }}
@if($quote->variant_price)
- Original Price: ${{ number_format($quote->variant_price, 2) }}
@endif

QUOTED PRICE: ${{ number_format($quote->quoted_price, 2) }}
@if($quote->quantity > 1)
Total: ${{ number_format($quote->quoted_price * $quote->quantity, 2) }}
@endif

@if($quote->admin_notes)
Additional Notes:
{{ $quote->admin_notes }}
@endif

Reply to this quote: {{ config('mail.from.address') }}

---
{{ config('app.name') }}
This quotation is valid for 30 days from the date of issue.
If you have any questions, please don't hesitate to contact us.