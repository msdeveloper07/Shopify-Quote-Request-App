<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\QuoteSubmissionMail;
use App\Mail\QuotationToCustomerMail;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Models\Shop;
use App\Models\Quote;

class QuoteController extends Controller
{
    public function index(Request $request)
    {
        $shop = $request->query("shop");
        
        if (!$shop) {
            return view('shopify.error', [
                'message' => 'Shop parameter is required'
            ]);
        }
        
        $shopRecord = Shop::where('shopify_domain', $shop)->first();
        if (!$shopRecord) {
            return view('shopify.not_installed', ['shop' => $shop]);
        }
        
        $quotesQuery = Quote::where("shop_domain", $shop);
        
        // Filter by status if provided
        if ($request->has('status') && $request->status != 'all') {
            $quotesQuery->where('status', $request->status);
        }
        
        $today = Carbon::now()->startOfDay();
        $week = Carbon::now()->startOfWeek();
        $month = Carbon::now()->startOfMonth();
        
        $summary = [
            "today" => Quote::where("shop_domain", $shop)->whereDate("created_at", $today)->count(),
            "week" => Quote::where("shop_domain", $shop)->where("created_at", ">=", $week)->count(),
            "month" => Quote::where("shop_domain", $shop)->where("created_at", ">=", $month)->count(),
            "lifetime" => Quote::where("shop_domain", $shop)->count(),
            "pending" => Quote::where("shop_domain", $shop)->where('status', 'pending')->count(),
            "in_progress" => Quote::where("shop_domain", $shop)->where('status', 'in_progress')->count(),
            "quotation_sent" => Quote::where("shop_domain", $shop)->where('status', 'quotation_sent')->count(),
        ];
        
        $quotes = $quotesQuery->orderBy('created_at', 'desc')->get();
        
        return view("shopify.dashboard", compact("quotes", "summary", "shop"));
    }
    
    /**
     * Show quote details in modal
     */
    public function show(Request $request, $id)
    {
        // echo"<pre>"; print_r($request->all());  die;
        $shop = $request->query("shop");
        $quote = Quote::where('shop_domain', $shop)->where('id', $id)->firstOrFail();
        return response()->json([
            'success' => true,
            'quote' => $quote
        ]);
    }
    
    /**
     * Update quote status
     */
    public function updateStatus(Request $request, $id)
    {
        $shop = $request->query("shop");
        
        $request->validate([
            'status' => 'required|in:pending,in_progress,quotation_sent,completed,cancelled',
            'admin_notes' => 'nullable|string|max:2000'
        ]);
        
        $quote = Quote::where('shop_domain', $shop)->where('id', $id)->firstOrFail();
        $quote->status = $request->status;
        
        if ($request->filled('admin_notes')) {
            $quote->admin_notes = $request->admin_notes;
        }
        
        $quote->save();
        
        Log::info("Quote status updated", [
            'quote_id' => $id,
            'new_status' => $request->status,
            'shop' => $shop
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'quote' => $quote
        ]);
    }
    
    /**
     * Send quotation to customer
     */
    public function sendQuotation(Request $request, $id)
    {
        $shop = $request->query("shop");
        
        $request->validate([
            'quoted_price' => 'required|numeric|min:0',
            'admin_notes' => 'nullable|string|max:2000',
            'message' => 'nullable|string|max:5000'
        ]);
        
        $quote = Quote::where('shop_domain', $shop)->where('id', $id)->firstOrFail();
        
        // Update quote with quotation details
        $quote->quoted_price = $request->quoted_price;
        $quote->admin_notes = $request->admin_notes;
        $quote->status = 'quotation_sent';
        $quote->quotation_sent_at = now();
        $quote->quotation_sent_by = 'Admin';
        $quote->save();
        
        try {
            Log::info("Attempting to send quotation email", [
                'quote_id' => $id,
                'customer_email' => $quote->email,
                'quoted_price' => $request->quoted_price
            ]);
            
            // Send email to customer
            Mail::to($quote->email)->send(new QuotationToCustomerMail($quote, $request->message));
            
            // If no exception, email sent successfully
            Log::info("Quotation email sent successfully", [
                'quote_id' => $id,
                'customer_email' => $quote->email
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Quotation sent successfully to ' . $quote->email
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to send quotation email", [
                'quote_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function proxyStoreOLDNOTINUSE(Request $request)
    {
        Log::info('=== PROXY REQUEST RECEIVED ===', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'query' => $request->query->all(),
            'post' => $request->post(),
            'input' => $request->input(),
            'content' => $request->getContent(),
            'headers' => $request->headers->all()
        ]);

        try {
            $jsonData = json_decode($request->getContent(), true);
            
            $allData = array_merge(
                $request->query->all(),
                $request->post(),
                is_array($jsonData) ? $jsonData : []
            );
            
            Log::info('Merged data:', ['data' => $allData]);
            
            $shop = $allData['shop'] ?? $request->get('shop') ?? $request->query('shop');
            
            if (!$shop) {
                Log::warning('No shop parameter found', ['all_data' => $allData]);
                
                return response()->json([
                    "error" => "Shop parameter is required"
                ], 400);
            }

            if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9\-]*\.myshopify\.com$/', $shop)) {
                Log::warning('Invalid shop domain', ['shop' => $shop]);
                
                return response()->json([
                    "error" => "Invalid shop domain"
                ], 400);
            }

            $shopRecord = Shop::where("shopify_domain", $shop)->first();
            
            if (!$shopRecord) {
                Log::warning('Shop not found', ['shop' => $shop]);
                
                return response()->json([
                    "error" => "Shop not found. Please install the app first.",
                    "shop" => $shop
                ], 404);
            }

            $validator = \Validator::make($allData, [
                "first_name" => "required|string|max:255",
                "last_name" => "required|string|max:255",
                "email" => "required|email|max:255",
                "phone" => [
                    "required",
                    "string",
                    "max:32",
                    'regex:/^\+?[0-9\s().-]{7,20}$/',
                ],
                "product_title" => "required|string|max:500",
                "product_id" => "nullable|string|max:100",
                "variant_id" => "nullable|string|max:100",
                "product_image" => "nullable|string|max:500",
                "product_url" => "nullable|string|max:500",
                "comments" => "nullable|string|max:2000",
                "selected_variant" => "nullable|string|max:500",
                "variant_price" => "nullable|numeric|min:0|max:999999.99",
                "quantity" => "nullable|numeric|min:0|max:99",
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed', [
                    'errors' => $validator->errors(),
                    'data' => $allData
                ]);
                
                return response()->json([
                    "error" => "Validation failed",
                    "details" => $validator->errors(),
                ], 422);
            }

            $validated = $validator->validated();

            // Create quote
            $quote = Quote::create([
                'shop_domain' => $shop,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'product_title' => $validated['product_title'],
                'product_id' => $validated['product_id'] ?? null,
                'variant_id' => $validated['variant_id'] ?? null,
                'product_image' => $validated['product_image'] ?? null,
                'product_url' => $validated['product_url'] ?? null,
                'comments' => $validated['comments'] ?? null,
                'selected_variant' => $validated['selected_variant'] ?? null,
                'variant_price' => $validated['variant_price'] ?? null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'quantity' => $validated['quantity'] ?? 1,
                'status' => 'pending',
            ]);

            Log::info("Quote created successfully", [
                "quote_id" => $quote->id,
                "shop" => $shop,
                "email" => $validated["email"]
            ]);

            // Testing
            // $data = $request->only(['first_name', 'last_name', 'email', 'phone', 'comments', 'product_title', 'selected_variant', 'variant_price']);
            

           // Send email to admin
            try {
                $recipientEmail = config('mail.quote_recipient', 'manpreet.cyberbells@yopmail.com');
                
                Log::info("Attempting to send admin email", [
                    'quote_id' => $quote->id,
                    'recipient' => $recipientEmail,
                    'mail_driver' => config('mail.default')
                ]);
                
                Mail::to($recipientEmail)->send(new QuoteSubmissionMail($validated, $shop));
                
                // If we get here without exception, email was sent successfully
                Log::info("Admin notification email sent successfully", [
                    'quote_id' => $quote->id,
                    'recipient' => $recipientEmail
                ]);
                
            } catch (\Exception $e) {
                Log::error("Email exception occurred", [
                    'quote_id' => $quote->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            // ================================
            // CREATE CUSTOMER IN SHOPIFY
            // ================================
            try {
                $accessToken = $shopRecord->access_token; // stored from OAuth
                $shopDomain  = $shopRecord->shopify_domain;

                $customerPayload = [
                    "customer" => [
                        "first_name"     => $validated["first_name"],
                        "last_name"      => $validated["last_name"],
                        "email"          => $validated["email"],
                        "phone"          => $validated["phone"],
                        "verified_email" => true,
                        "note"           => "Created automatically from Quote Request App"
                    ]
                ];

                $createCustomerUrl = "https://$shopDomain/admin/api/2024-10/customers.json";

                Log::info("Sending create-customer request to Shopify", [
                    "url" => $createCustomerUrl,
                    "payload" => $customerPayload
                ]);

                $customerResponse = Http::withHeaders([
                    "X-Shopify-Access-Token" => $accessToken,
                    "Content-Type" => "application/json"
                ])->post($createCustomerUrl, $customerPayload);

                $customerData = $customerResponse->json();

                if (!$customerResponse->successful()) {
                    Log::error("Failed to create Shopify customer", [
                        "response" => $customerData
                    ]);
                    $quote->update([
                        "shopify_customer_id" => $customerData['customer']['id'] ?? null
                    ]);
                } else {
                    Log::info("Shopify customer successfully created", $customerData);
                }

                // Get Customer ID (if already exists or newly created)
                $customerId = $customerData['customer']['id'] ?? null;

                if ($customerId) {
                    // ===== SEND ACCOUNT INVITE =====
                    $inviteUrl = "https://$shopDomain/admin/api/2024-10/customers/$customerId/send_invite.json";

                    Log::info("Sending account invite to Shopify customer", [
                        "customer_id" => $customerId,
                        "invite_url" => $inviteUrl
                    ]);

                    $inviteResponse = Http::withHeaders([
                        "X-Shopify-Access-Token" => $accessToken,
                        "Content-Type" => "application/json"
                    ])->post($inviteUrl, [
                        "customer_invite" => [
                            "to" => $validated['email'],
                            "from" => "info@cyberbells.ai",
                            "subject" => "Activate your account",
                            "custom_message" => "Thank you for requesting a quote! Please activate your account."
                        ]
                    ]);

                    $inviteData = $inviteResponse->json();
                    $quote->update([
                        "shopify_customer_id" => $customerId
                    ]);
                    if ($inviteResponse->successful()) {
                        Log::info("Account invite sent successfully", [
                            "customer_id" => $customerId,
                            "invite" => $inviteData
                        ]);
                    } else {
                        Log::error("Failed to send account invite", [
                            "customer_id" => $customerId,
                            "response" => $inviteData
                        ]);
                    }
                }

            } catch (\Exception $e) {
                Log::error("Shopify customer creation or invite failed", [
                    "error" => $e->getMessage(),
                    "trace" => $e->getTraceAsString(),
                ]);
            }

            return response()->json([
                "status" => "success",
                "message" => "Quote submitted successfully!",
                "quote_id" => $quote->id
            ], 201);

        } catch (\Exception $e) {
            Log::error("Quote submission failed", [
                "shop" => $request->get("shop"),
                "error" => $e->getMessage(),
                "trace" => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                "error" => "Internal server error",
                "message" => "Something went wrong. Please try again.",
                "debug" => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function proxyStore(Request $request)
    {
        Log::info('=== PROXY REQUEST RECEIVED ===', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'query' => $request->query->all(),
            'post' => $request->post(),
            'input' => $request->input(),
            'content' => $request->getContent(),
            'headers' => $request->headers->all()
        ]);

        try {
            $jsonData = json_decode($request->getContent(), true);
            $allData = array_merge(
                $request->query->all(),
                $request->post(),
                is_array($jsonData) ? $jsonData : []
            );
            
            Log::info('Merged data:', ['data' => $allData]);
            
            $shop = $allData['shop'] ?? $request->get('shop') ?? $request->query('shop');
            if (!$shop) {
                Log::warning('No shop parameter found', ['all_data' => $allData]);
                return response()->json([ "error" => "Shop parameter is required" ], 400);
            }

            if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9\-]*\.myshopify\.com$/', $shop)) {
                Log::warning('Invalid shop domain', ['shop' => $shop]);
                
                return response()->json([ "error" => "Invalid shop domain" ], 400);
            }

            $shopRecord = Shop::where("shopify_domain", $shop)->first();
            if (!$shopRecord) {
                Log::warning('Shop not found', ['shop' => $shop]);
                return response()->json([
                    "error" => "Shop not found. Please install the app first.",
                    "shop" => $shop
                ], 404);
            }

            $validator = \Validator::make($allData, [
                "first_name" => "required|string|max:255",
                "last_name" => "required|string|max:255",
                "email" => "required|email|max:255",
                "phone" => [
                    "required",
                    "string",
                    "max:32",
                    'regex:/^\+?[0-9\s().-]{7,20}$/',
                ],
                "product_title" => "required|string|max:500",
                "product_id" => "nullable|string|max:100",
                "variant_id" => "nullable|string|max:100",
                "product_image" => "nullable|string|max:500",
                "product_url" => "nullable|string|max:500",
                "comments" => "nullable|string|max:2000",
                "selected_variant" => "nullable|string|max:500",
                "variant_name" => "nullable|string|max:255",
                "variant_price" => "nullable|numeric|min:0|max:999999.99",
                "quantity" => "nullable|numeric|min:1|max:99",
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed', [
                    'errors' => $validator->errors(),
                    'data' => $allData
                ]);
                
                return response()->json([
                    "error" => "Validation failed",
                    "details" => $validator->errors(),
                ], 422);
            }

            $validated = $validator->validated();
            // ================================
            // CHECK FOR DUPLICATE QUOTE
            // ================================
            $duplicateQuote = Quote::where('shop_domain', $shop)->where('email', $validated['email'])->where('product_title', $validated['product_title'])->where('variant_name', $validated['variant_name'])->where('quantity', $validated['quantity'] ?? 1)->where('created_at', '>=', now()->subHours(24))->first();   // Within last 24 hours
            if ($duplicateQuote) {
                Log::warning('Duplicate quote attempt detected', [
                    'existing_quote_id' => $duplicateQuote->id,
                    'email' => $validated['email'],
                    'product' => $validated['product_title']
                ]);

                return response()->json([
                    "error" => "Duplicate quote request",
                    "message" => "You have already requested a quote for this product with the same quantity. Please check your email or contact us for updates.",
                    "existing_quote_id" => $duplicateQuote->id,
                    "submitted_at" => $duplicateQuote->created_at->diffForHumans()
                ], 409);
            }

            // ================================
            // CREATE CUSTOMER IN SHOPIFY FIRST
            // ================================
            $customerId = null;
            $customerCreationFailed = false;
            
            try {
                $accessToken = $shopRecord->access_token;
                $shopDomain = $shopRecord->shopify_domain;
                // Check if customer already exists in Shopify
                $searchCustomerUrl = "https://$shopDomain/admin/api/2024-10/customers/search.json?query=email:{$validated['email']}";
                
                Log::info("Searching for existing Shopify customer", [
                    "email" => $validated['email'],
                    "url" => $searchCustomerUrl
                ]);

                $searchResponse = Http::withHeaders([
                    "X-Shopify-Access-Token" => $accessToken,
                    "Content-Type" => "application/json"
                ])->get($searchCustomerUrl);

                $searchData = $searchResponse->json();

                if ($searchResponse->successful() && !empty($searchData['customers'])) {
                    // Customer already exists
                    $customerId = $searchData['customers'][0]['id'];
                    Log::info("Existing Shopify customer found", [
                        "customer_id" => $customerId,
                        "email" => $validated['email']
                    ]);
                } else {
                    // Create new customer
                    $customerPayload = [
                        "customer" => [
                            "first_name" => $validated["first_name"],
                            "last_name" => $validated["last_name"],
                            "email" => $validated["email"],
                            "phone" => $validated["phone"],
                            "verified_email" => true,
                            "note" => "Created automatically from Quote Request App"
                        ]
                    ];

                    $createCustomerUrl = "https://$shopDomain/admin/api/2024-10/customers.json";
                    Log::info("Creating new Shopify customer", [
                        "url" => $createCustomerUrl,
                        "payload" => $customerPayload
                    ]);

                    $customerResponse = Http::withHeaders([
                        "X-Shopify-Access-Token" => $accessToken,
                        "Content-Type" => "application/json"
                    ])->post($createCustomerUrl, $customerPayload);

                    $customerData = $customerResponse->json();
                    if (!$customerResponse->successful()) {
                        $customerCreationFailed = true;
                        
                        Log::error("Failed to create Shopify customer", [
                            "response" => $customerData,
                            "status" => $customerResponse->status()
                        ]);

                        // Extract actual error message
                        $errorMessage = "Unable to create your account in our system.";
                        
                        if (isset($customerData['errors'])) {
                            $errors = $customerData['errors'];
                            
                            // Handle different error formats
                            if (is_array($errors)) {
                                $errorMessages = [];
                                foreach ($errors as $field => $messages) {
                                    if (is_array($messages)) {
                                        $errorMessages[] = ucfirst($field) . " " . implode(', ', $messages);
                                    } else {
                                        $errorMessages[] = ucfirst($field) . " " . $messages;
                                    }
                                }
                                $errorMessage = implode('. ', $errorMessages);
                            } elseif (is_string($errors)) {
                                $errorMessage = $errors;
                            }
                        }

                        // Return error - don't proceed with quote
                        return response()->json([
                            "error" => "Customer creation failed",
                            "message" => $errorMessage,
                            "details" => $customerData['errors'] ?? null
                        ], 422); // Changed to 422 for validation errors
                    }

                    $customerId = $customerData['customer']['id'];
                    
                    Log::info("Shopify customer successfully created", [
                        "customer_id" => $customerId,
                        "customer_data" => $customerData
                    ]);
                }

            } catch (\Exception $e) {
                Log::error("Shopify customer creation/search failed", [
                    "error" => $e->getMessage(),
                    "trace" => $e->getTraceAsString(),
                ]);

                return response()->json([
                    "error" => "Customer account setup failed",
                    "message" => "We encountered an error while setting up your account. Please try again later.",
                    "debug" => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }

            // ================================
            // CREATE QUOTE (Only after successful customer creation)
            // ================================
            $quote = Quote::create([
                'shop_domain' => $shop,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'product_title' => $validated['product_title'],
                'product_id' => $validated['product_id'] ?? null,
                'variant_id' => $validated['selected_variant'] ?? null,
                'variant_name' => $validated['variant_name'] ?? null,
                'product_image' => $validated['product_image'] ?? null,
                'product_url' => $validated['product_url'] ?? null,
                'comments' => $validated['comments'] ?? null,
                'selected_variant' => $validated['selected_variant'] ?? null,
                'variant_price' => $validated['variant_price'] ?? null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'quantity' => $validated['quantity'] ?? 1,
                'status' => 'pending',
                'shopify_customer_id' => $customerId
            ]);

            Log::info("Quote created successfully", [
                "quote_id" => $quote->id,
                "shop" => $shop,
                "email" => $validated["email"],
                "shopify_customer_id" => $customerId
            ]);

            // ================================
            // SEND ACCOUNT INVITE (if new customer)
            // ================================
            if ($customerId && !isset($searchData['customers'])) {
                try {
                    $inviteUrl = "https://$shopDomain/admin/api/2024-10/customers/$customerId/send_invite.json";

                    Log::info("Sending account invite to Shopify customer", [
                        "customer_id" => $customerId,
                        "invite_url" => $inviteUrl
                    ]);

                    $inviteResponse = Http::withHeaders([
                        "X-Shopify-Access-Token" => $accessToken,
                        "Content-Type" => "application/json"
                    ])->post($inviteUrl, [
                        "customer_invite" => [
                            "to" => $validated['email'],
                            "subject" => "Activate your account at Shaw Supply",
                            "custom_message" => "Thank you for requesting a quote! Please activate your account to track your quote and place orders."
                        ]
                    ]);

                    $inviteData = $inviteResponse->json();

                    if ($inviteResponse->successful()) {
                        Log::info("Account invite sent successfully", [
                            "customer_id" => $customerId,
                            "invite" => $inviteData
                        ]);
                    } else {
                        Log::warning("Failed to send account invite (non-critical)", [
                            "customer_id" => $customerId,
                            "response" => $inviteData
                        ]);
                        // Don't fail the entire process if invite fails
                    }
                } catch (\Exception $e) {
                    Log::warning("Account invite failed (non-critical)", [
                        "customer_id" => $customerId,
                        "error" => $e->getMessage()
                    ]);
                    // Don't fail the entire process
                }
            }

            // ================================
            // SEND EMAIL TO ADMIN
            // ================================
            try {
                $recipientEmail = config('mail.quote_recipient', 'manpreet.cyberbells@yopmail.com');
                
                Log::info("Attempting to send admin email", [
                    'quote_id' => $quote->id,
                    'recipient' => $recipientEmail,
                    'mail_driver' => config('mail.default')
                ]);
                
                Mail::to($recipientEmail)->send(new QuoteSubmissionMail($validated, $shop));
                
                Log::info("Admin notification email sent successfully", [
                    'quote_id' => $quote->id,
                    'recipient' => $recipientEmail
                ]);
                
            } catch (\Exception $e) {
                Log::error("Email exception occurred (non-critical)", [
                    'quote_id' => $quote->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Don't fail the quote submission if email fails
            }

            return response()->json([
                "status" => "success",
                "message" => "Quote submitted successfully! We'll get back to you soon.",
                "quote_id" => $quote->id,
                "customer_id" => $customerId
            ], 201);

        } catch (\Exception $e) {
            Log::error("Quote submission failed", [
                "shop" => $request->get("shop"),
                "error" => $e->getMessage(),
                "trace" => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                "error" => "Internal server error",
                "message" => "Something went wrong. Please try again.",
                "debug" => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function customerQuotes(Request $request)
    {
        $email = $request->query('email');
        $customerId = $request->query('customer_id');

        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => 'Email is required'
            ], 400);
        }

        // Fetch all quotes for customer
        $quotes = Quote::where('email', $email)->orderBy('created_at', 'desc')->get();

        // Stats
        $submitted = $quotes->count();
        $pending = $quotes->where('status', 'pending')->count();
        $quoted = $quotes->where('status', 'quotation_sent')->count();
        $rejected = $quotes->where('status', 'rejected')->count();
        $lastActivity = optional($quotes->first())->created_at;

        return response()->json([
            "success" => true,
            "customer" => [
                "email" => $email,
                "customer_id" => $customerId,
                "submitted_quotes" => $submitted,
                "pending_quotes" => $pending,
                "quoted_by_admin" => $quoted,
                "rejected_quotes" => $rejected,
                "last_activity" => $lastActivity ? $lastActivity->toDateTimeString() : null,
            ],
            "quotes" => $quotes->map(function ($q) {
                return [
                    "id" => $q->id,
                    "status" => $q->status,
                    "status_label" => ucfirst($q->status),
                    "product_title" => $q->product_title,
                    "product_image" => $q->product_image,
                    "product_url" => $q->product_url,
                    "selected_variant" => $q->selected_variant,
                    "variant_name" => $q->variant_name,
                    "variant_price" => $q->variant_price,
                    "quantity" => $q->quantity,
                    "comments" => $q->comments,
                    "quoted_price" => $q->quoted_price,
                    "admin_notes" => $q->admin_notes,
                    "admin_message" => $q->quotation_message,
                    "created_at" => $q->created_at->toDateTimeString(),
                    "updated_at" => $q->updated_at->toDateTimeString(),
                ];
            }),
        ]);
    }

    public function getCustomerData(Request $request)
    {
        try {
            $shop = $request->query('shop');
            
            if (!$shop) {
                return response()->json(['error' => 'Shop parameter required'], 400);
            }
            
            $shopRecord = Shop::where('shopify_domain', $shop)->first();
            
            if (!$shopRecord) {
                return response()->json(['error' => 'Shop not found'], 404);
            }
            
            // Get customer ID from query (passed from frontend)
            $customerId = $request->query('customer_id');
            
            if (!$customerId) {
                return response()->json(['error' => 'Not logged in'], 401);
            }
            
            // Fetch customer from Shopify
            $accessToken = $shopRecord->access_token;
            $shopDomain = $shopRecord->shopify_domain;
            
            $customerUrl = "https://$shopDomain/admin/api/2024-10/customers/$customerId.json";
            
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $accessToken,
                'Content-Type' => 'application/json'
            ])->get($customerUrl);
            
            if (!$response->successful()) {
                return response()->json(['error' => 'Failed to fetch customer'], 500);
            }
            
            $customerData = $response->json();
            $customer = $customerData['customer'];
            
            return response()->json([
                'first_name' => $customer['first_name'] ?? '',
                'last_name' => $customer['last_name'] ?? '',
                'email' => $customer['email'] ?? '',
                'phone' => $customer['phone'] ?? ($customer['default_address']['phone'] ?? '')
            ]);
            
        } catch (\Exception $e) {
            Log::error('Get customer data failed', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Internal error'], 500);
        }
    }

}