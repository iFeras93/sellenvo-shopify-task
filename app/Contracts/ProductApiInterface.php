<?php

namespace App\Contracts;

use Illuminate\Http\Request;

interface ProductApiInterface
{
    public function getProducts();

    public function storeProduct(Request $request);
}
