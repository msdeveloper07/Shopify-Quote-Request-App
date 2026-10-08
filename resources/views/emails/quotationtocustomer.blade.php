<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 20px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background-color: #f4f6f9; line-height: 1.6;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
        <!-- Header -->
        <tr>
            <td style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: #4a5568; padding: 40px 30px; text-align: center;">
                <h1 style="font-size: 28px; font-weight: 700; margin: 0 0 8px 0; color: #4a5568;">💰 Your Quotation</h1>
                <p style="font-size: 16px; margin: 0; opacity: 0.95; color: #4a5568;">Quote Reference: #{{ $quote->id }}</p>
                <div style="display: inline-block; background: rgba(255, 255, 255, 0.2); padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; margin-top: 15px; color: #4a5568;">OFFICIAL QUOTE</div>
            </td>
        </tr>
        
        <!-- Content -->
        <tr>
            <td style="padding: 40px 30px;">
                <!-- Greeting -->
                <p style="font-size: 16px; color: #2d3748; margin: 0 0 20px 0;">
                    Dear <strong>{{ $quote->first_name }} {{ $quote->last_name }}</strong>,
                </p>
                
                <div style="font-size: 16px; color: #555555; margin-bottom: 30px; padding: 20px; background: #f8f9fa; border-left: 4px solid #28a745; border-radius: 4px;">
                    ✅ Thank you for your interest in our products. We're pleased to provide you with the following quotation:
                </div>
                
                @if($customMessage)
                <!-- Custom Message -->
                <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; border-radius: 4px; margin: 20px 0;">
                    <div style="font-weight: 700; color: #f57c00; margin-bottom: 10px; font-size: 14px; text-transform: uppercase;">💬 Message from Shaw Supply</div>
                    <div style="color: #5d4037; font-size: 14px; line-height: 1.8;">{{ $customMessage }}</div>
                </div>
                @endif
                
                @if($quote->product_image)
                <!-- Product Image -->
                <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                    <tr>
                        <td style="text-align: center;">
                            <img src="{{ $quote->product_image }}" alt="{{ $quote->product_title }}" style="max-width: 200px; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        </td>
                    </tr>
                </table>
                @endif
                
                <!-- Product Details -->
                <h2 style="font-size: 18px; font-weight: 700; color: #2d3748; margin: 30px 0 20px 0; padding-bottom: 10px; border-bottom: 2px solid #e2e8f0; border-left: 4px solid #28a745; padding-left: 12px;">📦 Product Details</h2>
                
                <table width="100%" cellpadding="0" cellspacing="0" style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                    <tr>
                        <td style="padding: 12px 20px 12px 0; font-weight: 600; color: #4a5568; font-size: 14px; width: 140px; vertical-align: top;">Product:</td>
                        <td style="padding: 12px 0; color: #2d3748; font-size: 14px; vertical-align: top;">
                            <strong>{{ $quote->product_title }}</strong>
                        </td>
                    </tr>
                    
                    @if(!empty($quote->variant_name) || !empty($quote->selected_variant))
                        <tr>
                            <td style="padding: 12px 20px 12px 0; font-weight: 600; color: #4a5568; font-size: 14px; width: 140px; vertical-align: top;">Variant:</td>
                            <td style="padding: 12px 0; color: #2d3748; font-size: 14px; vertical-align: top;">
                                {{ !empty($quote->variant_name) ? $quote->variant_name : $quote->selected_variant }}
                            </td>
                        </tr>
                    @endif
                    
                    <tr>
                        <td style="padding: 12px 20px 12px 0; font-weight: 600; color: #4a5568; font-size: 14px; width: 140px; vertical-align: top;">Quantity:</td>
                        <td style="padding: 12px 0; color: #2d3748; font-size: 14px; vertical-align: top;">
                            <strong>{{ $quote->quantity }} {{ $quote->quantity > 1 ? 'units' : 'unit' }}</strong>
                        </td>
                    </tr>
                    
                    @if($quote->variant_price)
                    <tr>
                        <td style="padding: 12px 20px 12px 0; font-weight: 600; color: #4a5568; font-size: 14px; width: 140px; vertical-align: top;">Original Price:</td>
                        <td style="padding: 12px 0; color: #2d3748; font-size: 14px; vertical-align: top;">
                            ${{ number_format($quote->variant_price, 2) }}
                        </td>
                    </tr>
                    @endif
                </table>
                
                <!-- Quoted Price - Big Highlight -->
                <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                    <tr>
                        <td style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: #4a5568; padding: 30px; border-radius: 12px; text-align: center; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);">
                            <div style="font-size: 16px; margin-bottom: 10px; color: #4a5568; opacity: 0.9; font-weight: 600;">YOUR QUOTED PRICE</div>
                            <div style="font-size: 48px; font-weight: 700; color: #4a5568; margin: 10px 0;">${{ number_format($quote->quoted_price, 2) }}</div>
                            @if($quote->quantity > 1)
                            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.3); font-size: 16px; color: #4a5568;">
                                <strong>Total Amount: ${{ number_format($quote->quoted_price * $quote->quantity, 2) }}</strong>
                            </div>
                            @endif
                            <div style="margin-top: 20px; font-size: 13px; color: #4a5568; opacity: 0.8;">
                                ⏰ This quote is valid for 30 days
                            </div>
                        </td>
                    </tr>
                </table>
                
                @if($quote->admin_notes)
                <!-- Additional Notes -->
                <div style="background: #e7f3ff; border-left: 4px solid #0066cc; padding: 20px; border-radius: 4px; margin: 20px 0;">
                    <div style="font-weight: 700; color: #0066cc; margin-bottom: 10px; font-size: 14px; text-transform: uppercase;">📝 Additional Notes</div>
                    <div style="color: #2d3748; font-size: 14px; line-height: 1.8;">{{ $quote->admin_notes }}</div>
                </div>
                @endif
                
                <!-- Divider -->
                <!-- <div style="height: 1px; background: #e2e8f0; margin: 30px 0;"></div> -->
                
                <!-- Next Steps -->
                <!-- <h2 style="font-size: 18px; font-weight: 700; color: #2d3748; margin: 30px 0 20px 0; text-align: center;">What's Next?</h2> -->
                
                <!-- <table width="100%" cellpadding="0" cellspacing="0" style="margin: 20px 0;">
                    <tr>
                        <td style="padding: 15px; text-align: center; background: #f8f9fa; border-radius: 8px;">
                            <div style="font-size: 40px; margin-bottom: 10px;">✅</div>
                            <div style="font-weight: 600; color: #2d3748; margin-bottom: 5px;">Accept Quote</div>
                            <div style="font-size: 13px; color: #718096;">Reply to this email to proceed</div>
                        </td>
                    </tr>
                </table> -->
                
                <!-- <table width="100%" cellpadding="0" cellspacing="0" style="margin: 20px 0;">
                    <tr>
                        <td style="padding: 15px; text-align: center; background: #f8f9fa; border-radius: 8px;">
                            <div style="font-size: 40px; margin-bottom: 10px;">❓</div>
                            <div style="font-weight: 600; color: #2d3748; margin-bottom: 5px;">Have Questions?</div>
                            <div style="font-size: 13px; color: #718096;">We're here to help you</div>
                        </td>
                    </tr>
                </table> -->
                
                <!-- Call to Action -->
                <!-- <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                    <tr>
                        <td style="text-align: center;">
                            <a href="mailto:{{ config('mail.from.address') }}" style="display: inline-block; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: #4a5568; padding: 16px 40px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);">📧 Reply to This Quote</a>
                        </td>
                    </tr>
                </table> -->
                
                <!-- <p style="text-align: center; color: #718096; font-size: 14px; margin: 20px 0;">
                    or call us directly at your convenience
                </p> -->
            </td>
        </tr>
        
        <!-- Footer -->
        <tr>
            <td style="background: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #e2e8f0;">
                <p style="color: #2d3748; font-size: 16px; margin: 5px 0; font-weight: 700;">Shaw Supply Company</p>
                <p style="color: #718096; font-size: 13px; margin: 15px 0 5px 0;">
                    📧 {{ config('mail.from.address') }}
                </p>
                
                <div style="height: 1px; background: #e2e8f0; margin: 25px auto; max-width: 300px;"></div>
                
                <p style="color: #718096; font-size: 13px; margin: 15px 0;">
                    <strong>Important:</strong> This quotation is valid for 30 days from the date of issue.
                </p>
                <p style="color: #718096; font-size: 13px; margin: 5px 0;">
                    If you have any questions, please don't hesitate to contact us.
                </p>
                
                <p style="color: #a0aec0; font-size: 12px; margin: 25px 0 5px 0;">
                    © {{ date('Y') }} Shaw Supply. All rights reserved.
                </p>
                
                <p style="color: #cbd5e0; font-size: 11px; margin: 10px 0 0 0;">
                    You received this email because you requested a quote from Shaw Supply.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>