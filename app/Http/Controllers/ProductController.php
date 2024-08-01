<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function getProductsList()
    {
        $api_key = config('services.shopify.api_key');
        $api_password = config('services.shopify.admin_api_token');
        $store_name = config('services.shopify.store_name');

        $client = new Client();
        $response = $client->get("https://$api_key:$api_password@$store_name.myshopify.com/admin/products.json");

        $products = json_decode($response->getBody(), true);
        return $products;
    }

}
