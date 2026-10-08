<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyShopifyEmbedded
{
    public function handle(Request $request, Closure $next)
    {
        $shop = $request->query('shop') ?? $request->query('embedded');
        
        if (!$shop) {
            Log::warning('No shop parameter found in embedded request');
            return response()->json(['error' => 'Shop parameter required'], 400);
        }

        // Store shop in session for later use
        session(['shopify_shop' => $shop]);
        
        return $next($request);
    }
}