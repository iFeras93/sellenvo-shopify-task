<?php

namespace App\Factories;

use App\Contracts\ProductApiInterface;
use App\Helpers\ShopifyUtils;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShopifyProductApi implements ProductApiInterface
{
    private Client $client;
    private string $storeName;
    private string $apiPassword;
    private string $apiKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = config('services.shopify.api_key');
        $this->apiPassword = config('services.shopify.admin_api_token');
        $this->storeName = config('services.shopify.store_name');
    }

    public function getProducts()
    {
        try {
            $response = $this->client->get("https://{$this->apiKey}:{$this->apiPassword}@{$this->storeName}.myshopify.com/admin/products.json");
            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $body = $response->getBody();
                $error = json_decode($body, true);
                return ['error' => $error['errors']];
            } else {
                return ['error' => $e->getMessage()];
            }
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function storeProduct(array|Request $product)
    {
        try {
//            Log::info("Pushed Product Object");
//            Log::info($product);
            $response = $this->client->post("https://{$this->apiKey}:{$this->apiPassword}@{$this->storeName}.myshopify.com/admin/products.json", [
                'json' => $product
            ]);

            $productFromShopify = json_decode($response->getBody()->getContents(), true);
//            Log::info($productFromShopify['product']);
//            $variants = collect($productFromShopify['product']['variants']) ?? [];
            //updates quantity for product
//            Log::info($location['id']);
//            $location = $this->fetchInventoryLocations();
//            Log::info("Variants:");
//            Log::info($variants);
//            Log::info("Location:");
//            Log::info($location['id']);
//            foreach ($variants as $variant) {
//                $this->updateInventoryQuantity($variant['id'], $location['id']);
//            }
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $body = $response->getBody();
                $error = json_decode($body, true);
                return ['error' => $error['errors']];
            } else {
                return ['error' => $e->getMessage()];
            }
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /****
     * These functions work good but i have another way to update inventory quantity.
     */

    public function fetchInventoryLocations()
    {
        try {
            $response = $this->client->get("https://{$this->apiKey}:{$this->apiPassword}@{$this->storeName}.myshopify.com/admin/locations.json");
            $location = json_decode($response->getBody(), true)['locations'][0];
            return $location ?? null;
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $body = $response->getBody();
                $error = json_decode($body, true);
                return ['error' => $error['errors']];
            } else {
                return ['error' => $e->getMessage()];
            }
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function updateInventoryQuantity($inventoryItemId, $locationID, $quantity = 50)
    {

        try {
            $inventoryLevelUrl = "https://{$this->apiKey}:{$this->apiPassword}@{$this->storeName}/admin/api/2023-04/inventory_levels/set.json";
            $response = $this->client->post($inventoryLevelUrl, [
                'json' => [
                    'inventory_level' => [
                        'inventory_item_id' => $inventoryItemId,
                        'location_id' => $locationID, // Replace with your location ID
                        'available' => $quantity
                    ]
                ]
            ]);


            Log::info("Update Inventory Level: {$locationID} - {$quantity}");
            Log::info(json_decode($response->getBody()->getContents(), true));
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $body = $response->getBody();
                $error = json_decode($body, true);
                return ['error' => $error['errors']];
            } else {
                return ['error' => $e->getMessage()];
            }
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
