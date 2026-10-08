<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\QuoteSubmissionMail;
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
        
        $today = Carbon::now()->startOfDay();
        $week = Carbon::now()->startOfWeek();
        $month = Carbon::now()->startOfMonth();
        
        $summary = [
            "today" => (clone $quotesQuery)->whereDate("created_at", $today)->count(),
            "week" => (clone $quotesQuery)->where("created_at", ">=", $week)->count(),
            "month" => (clone $quotesQuery)->where("created_at", ">=", $month)->count(),
            "lifetime" => (clone $quotesQuery)->count(),
        ];
        
        $quotes = $quotesQuery->orderBy('created_at', 'desc')->paginate(20);
        
        return view("shopify.dashboard", compact("quotes", "summary", "shop"));
    }

    /**
     * Handle Shopify App Proxy requests
     * Shopify may send POST data as query params or in body
     */
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
            // Shopify App Proxy can send data in multiple ways:
            // 1. Query parameters (most common)
            // 2. POST body (JSON)
            // 3. Form data
            
            // Try to get data from JSON body first
            $jsonData = json_decode($request->getContent(), true);
            
            // Merge all possible data sources
            $allData = array_merge(
                $request->query->all(),
                $request->post(),
                is_array($jsonData) ? $jsonData : []
            );
            
            Log::info('Merged data:', ['data' => $allData]);
            
            // Get shop parameter
            $shop = $allData['shop'] ?? $request->get('shop') ?? $request->query('shop');
            
            if (!$shop) {
                Log::warning('No shop parameter found', [
                    'all_data' => $allData
                ]);
                
                return response()->json([
                    "error" => "Shop parameter is required"
                ], 400);
            }

            // Validate shop domain
            if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9\-]*\.myshopify\.com$/', $shop)) {
                Log::warning('Invalid shop domain', ['shop' => $shop]);
                
                return response()->json([
                    "error" => "Invalid shop domain"
                ], 400);
            }

            // Check if shop exists
            $shopRecord = Shop::where("shopify_domain", $shop)->first();
            
            if (!$shopRecord) {
                Log::warning('Shop not found', ['shop' => $shop]);
                
                return response()->json([
                    "error" => "Shop not found. Please install the app first.",
                    "shop" => $shop
                ], 404);
            }

            // Validate required fields from merged data
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
                'comments' => $validated['comments'] ?? null,
                'selected_variant' => $validated['selected_variant'] ?? null,
                'variant_price' => $validated['variant_price'] ?? null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'quantity' => $validated['quantity'] ?? null,
            ]);

            Log::info("✅ Quote created successfully", [
                "quote_id" => $quote->id,
                "shop" => $shop,
                "email" => $validated["email"]
            ]);

            // Send email
            try {
                Mail::to(config('mail.quote_recipient', 'orders@thepatioliquidators.com'))
                    ->send(new QuoteSubmissionMail($validated, $shop));
                    
                Log::info("Email sent", ['quote_id' => $quote->id]);
            } catch (\Exception $e) {
                Log::error("Email failed", [
                    'quote_id' => $quote->id,
                    'error' => $e->getMessage()
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
}