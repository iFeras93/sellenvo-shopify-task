<?php

namespace App\Http\Controllers;

use App\Contracts\ProductApiInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productApi;

    public function __construct(ProductApiInterface $productApi)
    {
        $this->productApi = $productApi;
    }

    public function getProductsList()
    {
        return $this->productApi->getProducts();
    }


}
