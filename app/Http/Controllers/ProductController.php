<?php

namespace App\Http\Controllers;

use App\Contracts\ProductApiInterface;
use App\Imports\ProductsImport;
use App\Jobs\PrepareDataBeforePushToShopify;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    protected $productApi;

    public function __construct(ProductApiInterface $productApi)
    {
        $this->productApi = $productApi;
    }

    public function getProductsList()
    {
        $cleanedProducts = $this->modifyProductTitles($this->productApi->getProducts()['products']);
        return json_encode($cleanedProducts, JSON_PRETTY_PRINT);
    }

    public function handleStoringProduct()
    {
        $excelFile = Storage::path('public/products_export.csv');
        PrepareDataBeforePushToShopify::dispatch($excelFile);
        return 'Imported in progress... ' . time();
    }

    private function cleanObject(&$object, &$modifyTitle = false)
    {
        if (is_array($object)) {
            foreach ($object as $key => &$value) {
                if (is_array($value) || is_object($value)) {
                    $this->cleanObject($value, $modifyTitle);
                } else {
                    if ($value === 'N/A' || $value === '-' || $value === '' || $value === null) {
                        unset($object[$key]);
                        $modifyTitle = true;
                    }
                }
            }
        } elseif (is_object($object)) {
            foreach ($object as $key => &$value) {
                if (is_array($value) || is_object($value)) {
                    $this->cleanObject($value, $modifyTitle);
                } else {
                    if ($value === 'N/A' || $value === '-' || $value === '' || $value === null) {
                        unset($object->$key);
                        $modifyTitle = true;
                    }
                }
            }
        }

        return $object;
    }

    private function modifyProductTitles($products)
    {
        foreach ($products as &$product) {
            $modifyTitle = false;
            $this->cleanObject($product, $modifyTitle);
            if ($modifyTitle) {
                $product['title'] .= ' nullable';
            }
        }
        return $products;
    }

}
