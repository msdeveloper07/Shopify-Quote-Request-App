<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ShopifyEmbedMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $shop = $request->get('shop') ?: $request->header('X-Shopify-Shop-Domain');

        // Allow admin.shopify.com and the shop domain and *.myshopify.com
        $frameAncestors = "https://admin.shopify.com https://*.myshopify.com";
        if ($shop) {
            $frameAncestors .= " https://{$shop}";
        }

        $response->headers->set('Content-Security-Policy', "frame-ancestors {$frameAncestors};");

        return $response;
    }
}
