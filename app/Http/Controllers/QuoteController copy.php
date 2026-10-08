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
    /**
     * Display dashboard with quotes
     */
    public function index(Request $request)
    {
        $shop = $request->query("shop");
        
        // Validate shop parameter
        if (!$shop) {
            return view('shopify.error', [
                'message' => 'Shop parameter is required'
            ]);
        }
        
        // Check if shop is installed
        $shopRecord = Shop::where('shopify_domain', $shop)->first();
        if (!$shopRecord) {
            return view('shopify.not_installed', ['shop' => $shop]);
        }
        
        // Get quotes for this shop
        $quotesQuery = Quote::where("shop_domain", $shop);
        
        // Summary Stats
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
     * Store quote submission via App Proxy
     */
    public function proxyStore(Request $request)
    {
        try {
            // Validate shop parameter
            $shop = $request->get("shop");
            
            if (!$shop) {
                Log::warning('Quote submission without shop parameter', [
                    'ip' => $request->ip()
                ]);
                return response()->json([
                    "error" => "Shop parameter is required"
                ], 400);
            }

            // Check if shop is installed
            $shopRecord = Shop::where("shopify_domain", $shop)->first();
            
            if (!$shopRecord) {
                Log::warning('Quote submission for uninstalled shop', [
                    'shop' => $shop,
                    'ip' => $request->ip()
                ]);
                return response()->json([
                    "error" => "Shop not found. Please install the app first.",
                    "shop" => $shop
                ], 404);
            }

            // Validate input fields
            $validated = $request->validate([
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
            ]);

            // Create quote record
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
            ]);

            Log::info("Quote submitted successfully", [
                "quote_id" => $quote->id,
                "shop" => $shop,
                "customer_email" => $validated["email"],
                "product" => $validated["product_title"],
            ]);

            // Send email notification (with queue for production)
            try {
                Mail::to(config('mail.quote_recipient', 'orders@thepatioliquidators.com'))
                    ->queue(new QuoteSubmissionMail($validated, $shop));
                    
                Log::info("Quote email queued", [
                    "quote_id" => $quote->id
                ]);
            } catch (\Exception $e) {
                // Don't fail the request if email fails
                Log::error("Failed to queue quote email", [
                    "quote_id" => $quote->id,
                    "error" => $e->getMessage()
                ]);
            }

            return response()->json([
                "status" => "success",
                "message" => "Quote submitted successfully! We'll get back to you soon.",
                "quote_id" => $quote->id
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Quote validation failed', [
                'shop' => $request->get('shop'),
                'errors' => $e->errors()
            ]);
            
            return response()->json([
                "error" => "Validation failed",
                "details" => $e->errors(),
            ], 422);
            
        } catch (\Exception $e) {
            Log::error("Quote submission failed", [
                "shop" => $request->get("shop"),
                "error" => $e->getMessage(),
                "trace" => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                "error" => "Internal server error",
                "message" => "Something went wrong. Please try again later.",
            ], 500);
        }
    }
}