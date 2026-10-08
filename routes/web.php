<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopifyController;
use App\Http\Controllers\QuoteController;
use App\Models\Shop;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Artisan;

Route::get('/generate-key', function () {
    Artisan::call('key:generate');
    return '<h1>Application key generated successfully!</h1>';
});

Route::get('/clear-cache', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('optimize:clear');
    
    return '<h1>Config, cache, route, and view caches cleared!</h1>';
});

// OAuth routes
Route::get('/shopify/install', [ShopifyController::class, 'install'])->name('shopify.install');
Route::get('/shopify/callback', [ShopifyController::class, 'callback'])->name('shopify.callback');

// Embedded app entry point (this is what loads inside Shopify Admin)
Route::get('/dashboard', [QuoteController::class, 'index'])->name('dashboard');
// Admin quotes
Route::get('/admin/quotes', [QuoteController::class, 'index'])->name('quotes.index');
Route::get('/quotes/{id}', [QuoteController::class, 'show'])->name('quotes.show');
Route::post('/quotes/{id}/update-status', [QuoteController::class, 'updateStatus'])->name('quotes.update-status');
Route::post('/quotes/{id}/send-quotation', [QuoteController::class, 'sendQuotation'])->name('quotes.send-quotation');


// App Proxy Route
Route::any('proxy/quote-guard', [QuoteController::class, 'proxyStore'])->name('proxy.store');

// Webhook for uninstall
Route::post('/webhooks/app-uninstalled', [ShopifyController::class, 'uninstall'])->name('shopify.uninstall');

// Health check
Route::get('/health', function() {
    return response()->json(['status' => 'healthy', 'timestamp' => now()]);
});

// Landing page for direct access
Route::get('/', function () {
    $shop = request()->get('shop');
    
    if (!$shop) {
        return view('welcome');
    }
    
    $shopRecord = Shop::where('shopify_domain', $shop)->first();
    
    if ($shopRecord) {
        return redirect()->route('dashboard', ['shop' => $shop]);
    } else {
        return redirect()->route('shopify.install', ['shop' => $shop]);
    }
})->name('home');

Route::get('/api/customer/quotes', [QuoteController::class, 'customerQuotes']);
Route::get('/api/get-customer-data', [QuoteController::class, 'getCustomerData'])->name('get-customer-data');

Route::get('/test-quote-email', function() {
    try {
        // Dummy validated data (same structure as your controller)
        $validated = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'customer.cyberbells@yopmail.com',
            'phone' => '+1234567890',
            'product_title' => 'Test Product - Gold Bright PVD',
            'product_id' => 'test-123',
            'variant_id' => '45631465259198',
            'product_image' => '//cdn.shopify.com/s/files/1/test-image.png',
            'product_url' => 'https://shaw-supply.myshopify.com/products/test-product',
            'comments' => 'This is a test comment for quote request.',
            'selected_variant' => 'Color: Gold / Size: Large',
            'variant_price' => 28.00,
            'quantity' => 2
        ];
        
        $shop = 'shaw-supply.myshopify.com';
        
        // Get recipient email
        $recipientEmail = 'manpreet.cyberbells1211@yopmail.com';
        
        \Log::info("Testing admin email", [
            'recipient' => $recipientEmail,
            'mail_driver' => config('mail.default'),
            'mail_host' => config('mail.host'),
            'mail_port' => config('mail.port'),
            'mail_encryption' => config('mail.encryption'),
            'mail_from' => config('mail.from.address')
        ]);
        
        // Send the email
        \Mail::to($recipientEmail)->send(new \App\Mail\QuoteSubmissionMail($validated, $shop));
        
        \Log::info("Test admin notification email sent successfully", [
            'recipient' => $recipientEmail
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Test email sent successfully to ' . $recipientEmail,
            'config' => [
                'driver' => config('mail.default'),
                'host' => config('mail.host'),
                'port' => config('mail.port'),
                'encryption' => config('mail.encryption'),
                'from' => config('mail.from.address')
            ]
        ]);
        
    } catch (\Exception $e) {
        \Log::error("Test email failed", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'config' => [
                'driver' => config('mail.default'),
                'host' => config('mail.host'),
                'port' => config('mail.port'),
                'encryption' => config('mail.encryption'),
                'from' => config('mail.from.address')
            ]
        ], 500);
    }
});

Route::get('/check-mail-config', function() {
    return response()->json([
        'MAIL_MAILER' => env('MAIL_MAILER'),
        'MAIL_HOST' => env('MAIL_HOST'),
        'MAIL_PORT' => env('MAIL_PORT'),
        'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION'),
        'config_host' => config('mail.mailers.smtp.host'),
        'config_port' => config('mail.mailers.smtp.port'),
    ]);
});

// Route::get('/test-email', function() {
//     try {
//         \Mail::raw('This is a test email from Shaw Supply', function($message) {
//             $message->to('manpreet.cyberbells@yopmail.com')
//                     ->subject('Test Email from Shaw Supply');
//         });
        
//         return 'Email sent! Check your inbox.';
//     } catch (\Exception $e) {
//         return 'Error: ' . $e->getMessage();
//     }
// });

// Route::get('/test-mailable', function() {
//     try {
//         $quote = \App\Models\Quote::latest()->first();
        
//         if (!$quote) {
//             return 'No quotes found in database';
//         }
        
//         \Mail::to('manpreet.cyberbells@yopmail.com')
//             ->send(new \App\Mail\QuotationToCustomerMail($quote, 'Test message'));
        
//         return 'Mailable sent! Check your inbox.';
//     } catch (\Exception $e) {
//         return 'Error: ' . $e->getMessage() . '<br><br>' . $e->getTraceAsString();
//     }
// });

// Route::get('/test-simple-mailable', function() {
//     try {
//         $quote = \App\Models\Quote::latest()->first();
        
//         if (!$quote) {
//             return 'No quotes found in database';
//         }
        
//         // Send using a closure instead of Mailable class
//         \Mail::send('emails.quotationtocustomer', [
//             'quote' => $quote,
//             'customMessage' => 'Test message'
//         ], function($message) use ($quote) {
//             $message->to('manpreet.cyberbells@yopmail.com')
//                    ->subject('Test Quotation - Quote #' . $quote->id);
//         });
        
//         return 'Email sent using Mail::send()! Check your inbox.';
        
//     } catch (\Exception $e) {
//         return 'Error: ' . $e->getMessage() . '<br><br>' . $e->getTraceAsString();
//     }
// });

// Route::get('/test-with-logging', function() {
//     try {
//         // Enable query logging
//         \DB::enableQueryLog();
        
//         // Get the Swift Mailer instance and set logger
//         $mailer = Mail::getSwiftMailer();
//         $logger = new \Swift_Plugins_Loggers_ArrayLogger();
//         $mailer->registerPlugin(new \Swift_Plugins_LoggerPlugin($logger));
        
//         $quote = \App\Models\Quote::latest()->first();
        
//         Mail::to('manpreet.cyberbells@yopmail.com')
//             ->send(new \App\Mail\QuotationToCustomerMail($quote, 'Test'));
        
//         // Get the log
//         $log = $logger->dump();
        
//         return '<pre>' . htmlspecialchars($log) . '</pre>';
        
//     } catch (\Exception $e) {
//         return 'Error: ' . $e->getMessage() . '<br><br>' . $e->getTraceAsString();
//     }
// });

// Route::get('/debug-mail', function() {
//     return [
//         'driver' => config('mail.default'),
//         'mailer' => config('mail.mailers.smtp'),
//         'from' => config('mail.from'),
//         'host' => config('mail.mailers.smtp.host'),
//         'port' => config('mail.mailers.smtp.port'),
//         'encryption' => config('mail.mailers.smtp.encryption'),
//         'username' => config('mail.mailers.smtp.username') ? 'SET' : 'NOT SET',
//         'password' => config('mail.mailers.smtp.password') ? 'SET (length: ' . strlen(config('mail.mailers.smtp.password')) . ')' : 'NOT SET',
//     ];
// });

// Route::get('/test-smtp-detailed', function() {
//     try {
//         $quote = \App\Models\Quote::latest()->first();
        
//         if (!$quote) {
//             return 'No quotes found in database';
//         }
        
//         // Log before sending
//         \Log::info('=== STARTING EMAIL TEST ===');
//         \Log::info('Mail Config:', [
//             'driver' => config('mail.default'),
//             'host' => config('mail.mailers.smtp.host'),
//             'port' => config('mail.mailers.smtp.port'),
//             'encryption' => config('mail.mailers.smtp.encryption'),
//             'username' => config('mail.mailers.smtp.username'),
//             'from' => config('mail.from'),
//         ]);
        
//         // Try to send
//         Mail::to('manpreet.cyberbells@yopmail.com')
//             ->send(new \App\Mail\QuotationToCustomerMail($quote, 'SMTP Test'));
        
//         \Log::info('Mail::send() completed without throwing exception');
        
//         // Check for failures
//         // $failures = Mail::failures();
//         // if (!empty($failures)) {
//         //     \Log::error('Mail had failures:', ['failures' => $failures]);
//         //     return 'Email sent but had failures: ' . json_encode($failures);
//         // }
        
//         \Log::info('=== EMAIL TEST COMPLETED SUCCESSFULLY ===');
        
//         return 'Email sent via SMTP! Check logs and inbox.';
        
//     } catch (\Symfony\Component\Mailer\Exception\TransportException $e) {
//         \Log::error('SMTP Transport Exception:', [
//             'message' => $e->getMessage(),
//             'code' => $e->getCode(),
//             'trace' => $e->getTraceAsString()
//         ]);
//         return 'SMTP Error: ' . $e->getMessage();
        
//     } catch (\Exception $e) {
//         \Log::error('General Exception:', [
//             'message' => $e->getMessage(),
//             'trace' => $e->getTraceAsString()
//         ]);
//         return 'Error: ' . $e->getMessage();
//     }
// });