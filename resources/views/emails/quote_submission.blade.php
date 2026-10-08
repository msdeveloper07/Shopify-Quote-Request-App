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
            <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #4a5568; padding: 40px 30px; text-align: center;">
                <h1 style="font-size: 28px; font-weight: 700; margin: 0 0 8px 0; color: #4a5568;">🎯 New Quote Request</h1>
                <p style="font-size: 16px; margin: 0; opacity: 0.95; color: #4a5568;">A customer is interested in your products</p>
                <div style="display: inline-block; background: rgba(255, 255, 255, 0.2); padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; margin-top: 15px; color: #4a5568;">HIGH PRIORITY</div>
            </td>
        </tr>
        
        <!-- Content -->
        <tr>
            <td style="padding: 40px 30px;">
                <!-- Intro -->
                <div style="font-size: 16px; color: #555555; margin-bottom: 30px; padding: 20px; background: #f8f9fa; border-left: 4px solid #667eea; border-radius: 4px;">
                    📦 You have received a new quote request from <strong style="color: #667eea;">{{ $shop }}</strong>
                </div>
                
                <!-- Customer Information -->
                <h2 style="font-size: 18px; font-weight: 700; color: #2d3748; margin: 30px 0 20px 0; padding-bottom: 10px; border-bottom: 2px solid #e2e8f0; border-left: 4px solid #667eea; padding-left: 12px;">👤 Customer Information</h2>
                
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 10px;">
                    <tr>
                        <td style="padding: 12px 20px 12px 0; font-weight: 600; color: #4a5568; font-size: 14px; width: 140px; vertical-align: top;">Name:</td>
                        <td style="padding: 12px 0; color: #2d3748; font-size: 14px; vertical-align: top;">
                            <strong>{{ $quoteData['first_name'] }} {{ $quoteData['last_name'] }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 20px 12px 0; font-weight: 600; color: #4a5568; font-size: 14px; width: 140px; vertical-align: top;">Email:</td>
                        <td style="padding: 12px 0; color: #2d3748; font-size: 14px; vertical-align: top;">
                            <a href="mailto:{{ $quoteData['email'] }}" style="color: #667eea; text-decoration: none;">{{ $quoteData['email'] }}</a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 20px 12px 0; font-weight: 600; color: #4a5568; font-size: 14px; width: 140px; vertical-align: top;">Phone:</td>
                        <td style="padding: 12px 0; color: #2d3748; font-size: 14px; vertical-align: top;">
                            <a href="tel:{{ $quoteData['phone'] ?? '' }}" style="color: #667eea; text-decoration: none;">{{ $quoteData['phone'] ?? 'Not provided' }}</a>
                        </td>
                    </tr>
                </table>
                
                <!-- Divider -->
                <div style="height: 1px; background: #e2e8f0; margin: 25px 0;"></div>
                
                <!-- Product Information -->
                <h2 style="font-size: 18px; font-weight: 700; color: #2d3748; margin: 30px 0 20px 0; padding-bottom: 10px; border-bottom: 2px solid #e2e8f0; border-left: 4px solid #667eea; padding-left: 12px;">📦 Product Details</h2>
                
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 10px;">
                    <tr>
                        <td style="padding: 12px 20px 12px 0; font-weight: 600; color: #4a5568; font-size: 14px; width: 140px; vertical-align: top;">Product:</td>
                        <td style="padding: 12px 0; color: #2d3748; font-size: 14px; vertical-align: top;">
                            <strong>{{ $quoteData['product_title'] }}</strong>
                        </td>
                    </tr>
                    
                    @if(!empty($quoteData['variant_name']) || !empty($quoteData['selected_variant']))
                        <tr>
                            <td style="padding: 12px 20px 12px 0; font-weight: 600; color: #4a5568; font-size: 14px; width: 140px; vertical-align: top;">Variant:</td>
                            <td style="padding: 12px 0; color: #2d3748; font-size: 14px; vertical-align: top;">
                                {{ !empty($quoteData['variant_name']) ? $quoteData['variant_name'] : $quoteData['selected_variant'] }}
                            </td>
                        </tr>
                    @endif
                    
                    @if(isset($quoteData['quantity']))
                    <tr>
                        <td style="padding: 12px 20px 12px 0; font-weight: 600; color: #4a5568; font-size: 14px; width: 140px; vertical-align: top;">Quantity:</td>
                        <td style="padding: 12px 0; color: #2d3748; font-size: 14px; vertical-align: top;">
                            <strong>{{ $quoteData['quantity'] }} {{ $quoteData['quantity'] > 1 ? 'units' : 'unit' }}</strong>
                        </td>
                    </tr>
                    @endif
                </table>
                
                @if(isset($quoteData['variant_price']))
                <!-- Price Highlight -->
                <table width="100%" cellpadding="0" cellspacing="0" style="margin: 25px 0;">
                    <tr>
                        <td style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: #4a5568; padding: 20px; border-radius: 8px; text-align: center;">
                            <div style="font-size: 14px; margin-bottom: 8px; color: #4a5568; opacity: 0.9;">Product Price</div>
                            <div style="font-size: 32px; font-weight: 700; color: #4a5568; margin: 0;">${{ number_format($quoteData['variant_price'], 2) }}</div>
                            @if(isset($quoteData['quantity']) && $quoteData['quantity'] > 1)
                            <div style="margin-top: 10px; font-size: 14px; color: #4a5568; opacity: 0.9;">
                                Total: ${{ number_format($quoteData['variant_price'] * $quoteData['quantity'], 2) }}
                            </div>
                            @endif
                        </td>
                    </tr>
                </table>
                @endif
                
                @if(isset($quoteData['comments']) && !empty($quoteData['comments']))
                <!-- Comments -->
                <div style="background: #fff8e1; border-left: 4px solid #ffc107; padding: 20px; border-radius: 4px; margin: 20px 0;">
                    <div style="font-weight: 700; color: #f57c00; margin-bottom: 10px; font-size: 14px; text-transform: uppercase;">💬 Customer Comments</div>
                    <div style="color: #5d4037; font-size: 14px; line-height: 1.8; white-space: pre-wrap;">{{ $quoteData['comments'] }}</div>
                </div>
                @endif
                
                <!-- Call to Action -->
                <table width="100%" cellpadding="0" cellspacing="0" style="margin: 40px 0 30px 0; padding-top: 30px; border-top: 2px dashed #e2e8f0;">
                    <tr>
                        <td style="text-align: center;">
                            <p style="color: #718096; margin-bottom: 20px; font-size: 14px;">Click below to view this quote in your admin dashboard</p>
                            <a href="https://{{ $shop }}/admin/apps" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #4a5568; padding: 16px 40px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px;">📊 View in Dashboard →</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        
        <!-- Footer -->
        <tr>
            <td style="background: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #e2e8f0;">
                <p style="color: #2d3748; font-size: 14px; margin: 5px 0; font-weight: 600;">Shaw Supply Quote Management System</p>
                <p style="color: #718096; font-size: 13px; margin: 5px 0;">This is an automated notification from your quote management app.</p>
                <p style="color: #718096; font-size: 13px; margin: 15px 0 5px 0;">
                    Need help? Contact us at <a href="mailto:info.cyberbells@gmail.com" style="color: #667eea; text-decoration: none; font-weight: 600;">info.cyberbells@gmail.com</a>
                </p>
                <p style="color: #a0aec0; font-size: 12px; margin: 20px 0 5px 0;">© {{ date('Y') }} Shaw Supply. All rights reserved.</p>
            </td>
        </tr>
    </table>
</body>
</html>