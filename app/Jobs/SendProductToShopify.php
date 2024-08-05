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

//        $productData = [
//            'product' => [
//                'handle' => $product['handle'] ?? null,
//                'title' => $product['title'] ?? '',
//                'body_html' => $product['body_html'] ?? '',
//                'vendor' => $product['vendor'] ?? '',
//                'product_type' => $product['type'] ?? '',
//                'tags' => explode(', ', $product['tags']) ?? [],
//                'published' => filter_var($product['published'], FILTER_VALIDATE_BOOLEAN) ?? true,
//
////                'options' => [
////                    [
////                        'name' => $row['option1_name'],
////                        'values' => [$row['option1_value']]
////                    ],
////                    [
////                        'name' => $row['option2_name'],
////                        'values' => [$row['option2_value']]
////                    ],
////                    [
////                        'name' => $row['option3_name'],
////                        'values' => [$row['option3_value']]
////                    ]
////                ],
////                'variants' => [
////                    [
////                        'sku' => $row['variant_sku'],
////                        'weight' => (float)$row['variant_grams'],
////                        'inventory_management' => $row['variant_inventory_tracker'],
////                        'inventory_quantity' => 50,//$row['variant_inventory_qty'] ? (int)$row['variant_inventory_qty'] : null,
////                        'inventory_policy' => $row['variant_inventory_policy'],
////                        'fulfillment_service' => $row['variant_fulfillment_service'],
////                        'price' => (float)$row['variant_price'],
////                        'compare_at_price' => $row['variant_compare_at_price'] ? (float)$row['variant_compare_at_price'] : null,
////                        'requires_shipping' => filter_var($row['variant_requires_shipping'], FILTER_VALIDATE_BOOLEAN),
////                        'taxable' => filter_var($row['variant_taxable'], FILTER_VALIDATE_BOOLEAN),
////                        'barcode' => $row['variant_barcode'],
////                        'weight_unit' => $row['variant_weight_unit'],
////                        'tax_code' => $row['variant_tax_code']
////                    ]
////                ],
////                'images' => ShopifyUtils::handleImagesSrc($row),
//                'gift_card' => filter_var($product['gift_card'], FILTER_VALIDATE_BOOLEAN) ?? false,
//                'status' => $product['status'] ?? 'active',
//                'cost_per_item' => $product['cost_per_item'] ? (float)$product['cost_per_item'] : 0,
//            ]
//        ];

        $productData = [
            'product' => $product,
        ];

        // Remove empty options
        $productData['product']['images'] = array_filter($productData['product']['images'], function ($option) {
            return !is_null($option['src']);
        });

//        Log::info(json_encode($product));

        $productAfterRequest = (new ShopifyProductApi())->storeProduct($productData);
        Log::info($productAfterRequest);
    }


}
