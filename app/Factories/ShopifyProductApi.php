<?php

namespace App\Factories;

use App\Contracts\ProductApiInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;

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

    public function storeProduct(Request $request)
    {
        //
    }
}
