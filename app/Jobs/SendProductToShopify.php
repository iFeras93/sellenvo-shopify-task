<?php

namespace App\Jobs;

use App\Factories\ShopifyProductApi;
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
use function Pest\Laravel\json;

class SendProductToShopify implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    protected array $products;
    protected $productSerivce;

    /**
     * Create a new job instance.
     */
    public function __construct(array $products)
    {
        $this->products = $products;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $productsValues = collect($this->products)->values();

        foreach ($productsValues as $products) {
            $images = [];
            $productData = ShopifyUtils::collectCleanProductData($products[0]);
            $variants = ShopifyUtils::collectVariantsData($products[0]);

            foreach ($products as $product) {
                $inside_images = [];
                $images[] = array_merge($inside_images, ShopifyUtils::collectImagesData($product));
            }

            $productData['images'] = ShopifyUtils::handleImagesSrc($images)->toArray();
            $productData['variants'] = ShopifyUtils::mapVariants($variants)->toArray();
            $this->pushProduct($productData);
            Log::info("*****************************\n");
        }

    }

    public function pushProduct($product): void
    {
        $client = new Client();
        $apiKey = config('services.shopify.api_key');
        $apiPassword = config('services.shopify.admin_api_token');
        $storeName = config('services.shopify.store_name');

        $productData = [
            'product' => $product,
        ];

        // Remove empty options
        $productData['product']['images'] = array_filter($productData['product']['images'], function ($option) {
            return !is_null($option['src']);
        });

        $productAfterRequest = (new ShopifyProductApi())->storeProduct($productData);
        Log::info($productAfterRequest);
    }


}
