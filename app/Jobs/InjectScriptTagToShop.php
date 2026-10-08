<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InjectScriptTagToShop implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $shopDomain;
    protected $accessToken;
    
    public $tries = 3;
    public $backoff = [10, 30, 60]; // Progressive backoff
    public $timeout = 120;

    public function __construct($shopDomain, $accessToken)
    {
        $this->shopDomain = $shopDomain;
        $this->accessToken = $accessToken;
    }

    public function handle()
    {
        // Script files to inject
        $scripts = ['/shopify/quote.js'];
       
        foreach ($scripts as $scriptPath) {
            // $scriptUrl = rtrim(config('app.url'), '/') . $scriptPath . '?v=' . now()->timestamp;
            $scriptUrl = 'https://recruitment-wellness-represents-combinations.trycloudflare.com'.$scriptPath . '?v=' . now()->timestamp;
            try {
                Log::info("Starting script tag injection", [
                    'shop' => $this->shopDomain,
                    'script' => $scriptUrl
                ]);

                // Get existing script tags
                $existingResponse = Http::timeout(30)
                    ->withHeaders([
                        'X-Shopify-Access-Token' => $this->accessToken,
                        'Content-Type' => 'application/json'
                    ])
                    ->get("https://{$this->shopDomain}/admin/api/2025-10/script_tags.json");

                if (!$existingResponse->successful()) {
                    Log::warning("Failed to fetch existing script tags", [
                        'shop' => $this->shopDomain,
                        'status' => $existingResponse->status(),
                        'response' => $existingResponse->body()
                    ]);
                    
                    // If we can't fetch, continue to injection anyway
                }

                // Remove existing script tags with the same base URL
                // $baseScriptUrl = rtrim(config('app.url'), '/') . $scriptPath;
                $baseScriptUrl = 'https://recruitment-wellness-represents-combinations.trycloudflare.com'.$scriptPath;
                $deletedCount = 0;
                
                foreach ($existingResponse['script_tags'] ?? [] as $tag) {
                    if (strpos($tag['src'], $baseScriptUrl) === 0) {
                        $deleteResponse = Http::timeout(30)
                            ->withHeaders([
                                'X-Shopify-Access-Token' => $this->accessToken,
                                'Content-Type' => 'application/json'
                            ])
                            ->delete("https://{$this->shopDomain}/admin/api/2025-10/script_tags/{$tag['id']}.json");
                        
                        if ($deleteResponse->successful()) {
                            $deletedCount++;
                            Log::info("Deleted existing script tag", [
                                'shop' => $this->shopDomain,
                                'script_tag_id' => $tag['id'],
                                'src' => $tag['src']
                            ]);
                        }
                    }
                }

                // Small delay if we deleted tags
                if ($deletedCount > 0) {
                    sleep(2);
                }

                // Inject new script tag
                $injectResponse = Http::timeout(30)
                    ->withHeaders([
                        'X-Shopify-Access-Token' => $this->accessToken,
                        'Content-Type' => 'application/json'
                    ])
                    ->post("https://{$this->shopDomain}/admin/api/2025-10/script_tags.json", [
                        'script_tag' => [
                            'event' => 'onload',
                            'src' => $scriptUrl,
                            'display_scope' => 'all'
                        ]
                    ]);

                if ($injectResponse->successful() && in_array($injectResponse->status(), [200, 201])) {
                    Log::info("ScriptTag injected successfully", [
                        'shop' => $this->shopDomain,
                        'script' => $scriptUrl,
                        'id' => $injectResponse['script_tag']['id'] ?? 'unknown',
                        'deleted_old_tags' => $deletedCount
                    ]);
                } else {
                    $errorBody = $injectResponse->body();
                    
                    Log::error("Failed to inject ScriptTag", [
                        'shop' => $this->shopDomain,
                        'script' => $scriptUrl,
                        'status' => $injectResponse->status(),
                        'response' => $errorBody
                    ]);
                    
                    throw new \Exception("Failed to inject script tag: HTTP {$injectResponse->status()} - {$errorBody}");
                }

            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::error("Connection error during script tag injection", [
                    'shop' => $this->shopDomain,
                    'script' => $scriptPath,
                    'error' => $e->getMessage()
                ]);
                
                throw $e;
                
            } catch (\Exception $e) {
                Log::error("ScriptTag injection exception", [
                    'shop' => $this->shopDomain,
                    'script' => $scriptPath,
                    'error' => $e->getMessage(),
                    'attempt' => $this->attempts()
                ]);

                throw $e;
            }
        }
    }
    
    public function failed(\Throwable $exception)
    {
        Log::critical("ScriptTag injection failed permanently after all retries", [
            'shop' => $this->shopDomain,
            'error' => $exception->getMessage(),
            'attempts' => $this->tries
        ]);
        
        // Optional: Send notification to admin
        // Mail::to('admin@example.com')->send(new ScriptInjectionFailedMail($this->shopDomain));
    }
}