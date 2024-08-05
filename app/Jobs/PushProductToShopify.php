<?php

namespace App\Jobs;

use App\Helpers\ShopifyUtils;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PushProductToShopify implements ShouldQueue
{
    use Queueable;
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public $rows;

    /**
     * Create a new job instance.
     */
    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $client = new Client();
        $apiKey = config('services.shopify.api_key');
        $apiPassword = config('services.shopify.admin_api_token');
        $storeName = config('services.shopify.store_name');

        foreach ($this->rows as $row) {

            $data = (new ShopifyUtils())->post_or_put('post', $row);
            Log::info(json_encode($data));
            $productData = [
                'product' => [
                    'handle' => $row['handle'],
                    'title' => $row['title'],
                    'body_html' => $row['body_html'],
                    'vendor' => $row['vendor'],
                    'product_type' => $row['type'],
                    'tags' => explode(', ', $row['tags']),
                    'published' => filter_var($row['published'], FILTER_VALIDATE_BOOLEAN),
                    'options' => [
                        [
                            'name' => $row['option1_name'],
                            'values' => [$row['option1_value']]
                        ],
                        [
                            'name' => $row['option2_name'],
                            'values' => [$row['option2_value']]
                        ],
                        [
                            'name' => $row['option3_name'],
                            'values' => [$row['option3_value']]
                        ]
                    ],
                    'variants' => [
                        [
                            'sku' => $row['variant_sku'],
                            'weight' => (float)$row['variant_grams'],
                            'inventory_management' => $row['variant_inventory_tracker'],
                            'inventory_quantity' => 50,//$row['variant_inventory_qty'] ? (int)$row['variant_inventory_qty'] : null,
                            'inventory_policy' => $row['variant_inventory_policy'],
                            'fulfillment_service' => $row['variant_fulfillment_service'],
                            'price' => (float)$row['variant_price'],
                            'compare_at_price' => $row['variant_compare_at_price'] ? (float)$row['variant_compare_at_price'] : null,
                            'requires_shipping' => filter_var($row['variant_requires_shipping'], FILTER_VALIDATE_BOOLEAN),
                            'taxable' => filter_var($row['variant_taxable'], FILTER_VALIDATE_BOOLEAN),
                            'barcode' => $row['variant_barcode'],
                            'weight_unit' => $row['variant_weight_unit'],
                            'tax_code' => $row['variant_tax_code']
                        ]
                    ],
                    'images' => ShopifyUtils::handleImagesSrc($row),
                    'gift_card' => filter_var($row['gift_card'], FILTER_VALIDATE_BOOLEAN),
                    'status' => $row['status'] ?? 'active',
                    'cost_per_item' => $row['cost_per_item'] ? (float)$row['cost_per_item'] : null,
                ]
            ];

            // Remove empty options
            $productData['product']['options'] = array_filter($productData['product']['options'], function ($option) {
                return !is_null($option['name']);
            });

            try {
                $response = $client->post("https://{$apiKey}:{$apiPassword}@{$storeName}.myshopify.com/admin/api/2023-01/products.json", [
                    'json' => $productData
                ]);

                // json_decode($response->getBody(), true);
                Log::info($response->getStatusCode());
            } catch (RequestException $e) {
                if ($e->hasResponse()) {
                    $response = $e->getResponse();
                    $body = $response->getBody();
                    $error = json_decode($body, true);
                    //return ['error' => $error['errors']];
                    Log::error("RequestException::" . json_encode($error['errors']));
                } else {
                    //return ['error' => $e->getMessage()];
                    Log::error("RequestException::else::" . $e->getMessage());
                }
            } catch (GuzzleException $e) {
                Log::error("GuzzleException::" . $e->getMessage());
                //return ['error' => $e->getMessage()];
            }
        }
    }

    public function handle__(): void
    {
        $client = new Client();
        $apiKey = config('services.shopify.api_key');
        $apiPassword = config('services.shopify.admin_api_token');
        $storeName = config('services.shopify.store_name');

        foreach ($this->rows as $row) {
            $productData = (new ShopifyUtils())->post_or_put($row);

            try {
                $response = $client->post("https://{$apiKey}:{$apiPassword}@{$storeName}.myshopify.com/admin/api/2023-01/products.json", [
                    'json' => $productData
                ]);

                // json_decode($response->getBody(), true);
                //Log::info($response->getStatusCode());
            } catch (RequestException $e) {
                if ($e->hasResponse()) {
                    $response = $e->getResponse();
                    $body = $response->getBody();
                    $error = json_decode($body, true);
                    //return ['error' => $error['errors']];
                    Log::error("RequestException::" . json_encode($error['errors']));
                } else {
                    //return ['error' => $e->getMessage()];
                    Log::error("RequestException::else::" . $e->getMessage());
                }
            } catch (GuzzleException $e) {
                Log::error("GuzzleException::" . $e->getMessage());
                //return ['error' => $e->getMessage()];
            }
        }
    }

}
