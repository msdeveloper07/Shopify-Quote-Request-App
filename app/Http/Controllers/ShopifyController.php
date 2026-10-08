<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Shop;
use App\Jobs\InjectScriptTagToShop;

class ShopifyController extends Controller
{
    /**
     * Installation endpoint - redirects to Shopify OAuth
     */
    public function install(Request $request)
    {
        $shop = $request->get('shop');
        
        // Validate shop parameter
        if (!$shop || !$this->isValidShopDomain($shop)) {
            Log::warning('Invalid shop domain in install request', ['shop' => $shop]);
            abort(400, 'Invalid shop parameter');
        }
        
        $scopes = 'read_products,read_inventory,read_locations,read_script_tags,write_script_tags,read_customers,write_customers';
        $redirectUri = urlencode(config('shopify.url') . '/shopify/callback');
        $apiKey = config('shopify.api_key');
        $nonce = bin2hex(random_bytes(16));
        
        // Store nonce in CACHE instead of session (expires in 10 minutes)
        Cache::put("shopify_nonce_{$shop}", $nonce, now()->addMinutes(10));
        
        $installUrl = "https://{$shop}/admin/oauth/authorize?client_id={$apiKey}&scope={$scopes}&redirect_uri={$redirectUri}&state={$nonce}";
        
        Log::info('Redirecting to Shopify OAuth', [
            'shop' => $shop,
            'nonce' => $nonce
        ]);
        
        return redirect($installUrl);
    }

    /**
     * OAuth callback - handles access token exchange
     */
    public function callback(Request $request)
    {
        try {
            $shop = $request->get('shop');
            $code = $request->get('code');
            $requestState = $request->get('state');
            
            // Validate shop domain first
            if (!$this->isValidShopDomain($shop)) {
                Log::error('Invalid shop domain in callback', ['shop' => $shop]);
                abort(400, 'Invalid shop domain');
            }
            
            // Get nonce from CACHE instead of session
            $cachedNonce = Cache::get("shopify_nonce_{$shop}");
            
            Log::info('OAuth callback - State validation', [
                'shop' => $shop,
                'cached_nonce' => $cachedNonce,
                'request_state' => $requestState
            ]);
            
            // Validate state/nonce
            if (!$cachedNonce || $requestState !== $cachedNonce) {
                Log::error('Invalid state parameter in callback', [
                    'shop' => $shop,
                    'cached_nonce' => $cachedNonce,
                    'request_state' => $requestState
                ]);
                abort(403, 'Invalid state parameter. Please try installing again.');
            }
            
            // Clear the nonce from cache after validation
            Cache::forget("shopify_nonce_{$shop}");
            
            // Validate HMAC
            if (!$this->validateHmac($request->all(), $request->get('hmac'))) {
                Log::error('Invalid HMAC in callback', [
                    'shop' => $shop
                ]);
                abort(403, 'Invalid HMAC signature. Installation failed.');
            }
            
            // Validate authorization code
            if (!$code) {
                Log::error('Missing authorization code', ['shop' => $shop]);
                abort(400, 'Missing authorization code');
            }
            
            $apiSecret = config('shopify.api_secret');
            $apiKey = config('shopify.api_key');

            // Exchange code for access token
            $response = Http::timeout(30)->post("https://{$shop}/admin/oauth/access_token", [
                'client_id' => $apiKey,
                'client_secret' => $apiSecret,
                'code' => $code,
            ]);

            if (!$response->successful() || !isset($response['access_token'])) {
                Log::error('Failed to get access token', [
                    'shop' => $shop,
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                abort(500, 'Failed to authenticate with Shopify. Please try again.');
            }

            $accessToken = $response['access_token'];
            $associatedUser = $response['associated_user'] ?? null;
            
            // Store shop and access token
            Shop::updateOrCreate(
                ['shopify_domain' => $shop],
                [
                    'access_token' => $accessToken,
                    'scopes' => $response['scope'] ?? null,
                    'associated_user' => $associatedUser ? json_encode($associatedUser) : null,
                    'installed_at' => now(),
                ]
            );

            Log::info("Shop authenticated successfully", [
                'shop' => $shop,
                'scopes' => $response['scope'] ?? 'unknown'
            ]);

            // Dispatch job to inject script tag
            InjectScriptTagToShop::dispatch($shop, $accessToken);

            Log::info("ScriptTag injection job dispatched", [
                'shop' => $shop
            ]);
            
            // Redirect to embedded app with proper parameters
            $host = base64_encode("{$shop}/admin");
            $redirectUrl = "https://{$shop}/admin/apps/" . $apiKey;
            
            return view('shopify.installed', [
                'shop' => $shop,
                'host' => $host,
                'apiKey' => $apiKey,
                'redirectUrl' => $redirectUrl
            ]);
            
        } catch (\Exception $e) {
            Log::error('OAuth callback failed', [
                'shop' => $request->get('shop'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('shopify.error', [
                'message' => 'An error occurred during installation. Please try again or contact support.'
            ]);
        }
    }

    /**
     * Validate HMAC signature from Shopify
     */
    private function validateHmac($params, $hmac)
    {
        if (!$hmac) {
            return false;
        }
        
        $filteredParams = array_diff_key($params, array_flip(['hmac', 'signature']));
        ksort($filteredParams);
        
        $computedHmac = hash_hmac(
            'sha256', 
            http_build_query($filteredParams), 
            config('shopify.api_secret')
        );
        
        return hash_equals($computedHmac, $hmac);
    }
    
    /**
     * Validate shop domain format
     */
    private function isValidShopDomain($shop)
    {
        return preg_match('/^[a-zA-Z0-9][a-zA-Z0-9\-]*\.myshopify\.com$/', $shop);
    }
    
    /**
     * Uninstall webhook handler (optional but recommended)
     */
    public function uninstall(Request $request)
    {
        try {
            $shop = $request->header('X-Shopify-Shop-Domain');
            
            if (!$shop) {
                Log::error('Missing shop domain in uninstall webhook');
                return response()->json(['error' => 'Missing shop domain'], 400);
            }
            
            // Verify webhook authenticity
            if (!$this->verifyWebhook($request)) {
                Log::error('Invalid webhook signature', ['shop' => $shop]);
                return response()->json(['error' => 'Invalid signature'], 401);
            }
            
            // Delete shop record
            $deleted = Shop::where('shopify_domain', $shop)->delete();
            
            Log::info('Shop uninstalled', [
                'shop' => $shop,
                'deleted' => $deleted
            ]);
            
            return response()->json(['status' => 'success'], 200);
            
        } catch (\Exception $e) {
            Log::error('Uninstall webhook failed', [
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Internal error'], 500);
        }
    }
    
    /**
     * Verify Shopify webhook signature
     */
    private function verifyWebhook(Request $request)
    {
        $hmac = $request->header('X-Shopify-Hmac-Sha256');
        $data = $request->getContent();
        
        $calculatedHmac = base64_encode(
            hash_hmac('sha256', $data, config('shopify.api_secret'), true)
        );
        
        return hash_equals($hmac, $calculatedHmac);
    }
}