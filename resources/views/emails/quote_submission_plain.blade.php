New Quote Request PLAIN TEST

You have received a new quote request from {{ $shop }}

Customer Details:
- Name: {{ $quoteData['first_name'] }} {{ $quoteData['last_name'] }}
- Email: {{ $quoteData['email'] }}
- Phone: {{ $quoteData['phone'] ?? 'N/A' }}

Product Information:
- Product: {{ $quoteData['product_title'] }}
@if(isset($quoteData['selected_variant']))
- Variant: {{ $quoteData['selected_variant'] }}
@endif
@if(isset($quoteData['variant_price']))
- Price: ${{ number_format($quoteData['variant_price'], 2) }}
@endif
@if(isset($quoteData['quantity']))
- Quantity: {{ $quoteData['quantity'] }}
@endif

@if(isset($quoteData['comments']))
Customer Comments:
{{ $quoteData['comments'] }}
@endif

View in Dashboard: {{ config('app.url') }}/dashboard?shop={{ $shop }}