<?php

namespace App\Helpers;

use App\Models\AbstractModel;
use App\Models\Image;
use App\Models\Metafield;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ShopifyUtils
{

    protected $api = 'products';
    protected $ids = [];
    /** @var array $resource_models */
    protected static $resource_models = [
        'images' => Image::class,
        'metafields' => Metafield::class,
        'products' => Product::class,
        'variants' => Variant::class,
    ];

    /**
     * @var array Data types that should be expanded into an array.
     * @see https://shopify.dev/apps/metafields/types
     */
    public const JSON_TYPES = [
        'dimension',
        'json',
        'money',
        'rating',
        'volume',
    ];

    /**
     * @var string Prefix given to metafield types that are configured to accept multiple values and will be encoded as JSON.
     * @see https://shopify.dev/apps/metafields/types#list-types
     */
    public const LIST_PREFIX = 'list';

    public static function cleaningNullableProductsList($productArray)
    {
        //
    }

    public static function handleImagesSrc($images)
    {
        $imgArray = [];
        $collectedImagesArray = is_array($images) ? collect($images) : collect([$images]);
        return $collectedImagesArray->map(fn($image) => [
            'src' => $image['image_src'] ?? null,
            'position' => $image['image_position'] ?? null,
            'alt' => $image['image_alt_text'] ?? null,
        ])->merge($imgArray);
    }

    public static function collectImagesData($product): array
    {
        return collect($product)
            ->filter(fn($item, $key) => str_contains($key, 'image_'))
            ->toArray();
    }

    public static function collectVariantsData($product): array
    {
        $arrWithVariantsKey = collect($product)
            ->filter(fn($item, $key) => str_contains($key, 'variant_'));
        return $arrWithVariantsKey->toArray();
    }

    public static function collectOptionsData($product): array
    {
        $arrWithOptionsKey = collect($product)->filter(fn($item, $key) => str_contains($key, 'option'));
        return $arrWithOptionsKey->toArray();
    }

    public static function collectSeoData($product): array
    {
        $arrWithKeys = collect($product)->filter(fn($item, $key) => str_contains($key, 'seo_') || str_contains($key, 'google_'));
        return $arrWithKeys->toArray();
    }

    public static function collectCleanProductData($product): array
    {
        $arrWithKeys = collect($product)
            ->filter(fn($item, $key) => !preg_match('/^(image_|option|google_|seo_|variant_)/', $key));
        return $arrWithKeys->toArray();
    }


    public static function mapVariants($products)
    {
        $collectedProductArray = collect([$products]); //is_array($products) ? collect($products) : collect([$products]);
        return $collectedProductArray->map(fn($item) => [
            'sku' => $item['variant_sku'] ?? null,
            'weight' => (float)$item['variant_grams'] ?? null,
            'inventory_management' => $item['variant_inventory_tracker'] ?? 'shopify',
            'inventory_quantity' => 50,//$row['variant_inventory_qty'] ? (int)$row['variant_inventory_qty'] : null,
            'inventory_policy' => $item['variant_inventory_policy'] ?? null,
            'fulfillment_service' => $item['variant_fulfillment_service'] ?? null,
            'price' => (float)$item['variant_price'] ?? 0,
            'compare_at_price' => $item['variant_compare_at_price'] ? (float)$item['variant_compare_at_price'] : null,
            'requires_shipping' => filter_var($item['variant_requires_shipping'], FILTER_VALIDATE_BOOLEAN),
            'taxable' => filter_var($item['variant_taxable'], FILTER_VALIDATE_BOOLEAN), true,
            'barcode' => $item['variant_barcode'] ?? null,
            'weight_unit' => $item['variant_weight_unit'] ?? null,
            'tax_code' => $item['variant_tax_code'] ?? null
        ]);
    }
}
