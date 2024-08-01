<?php

namespace App\Helpers;

class ShopifyUtils
{
    public static function cleaningNullableProductsList($productArray)
    {
        //
    }

    public static function handleImagesSrc($images)
    {
        $imgArray = [];
        $collectedImagesArray = is_array($images) ? collect($images) : collect([$images]);

        return $collectedImagesArray->map(function ($image) {
            return ['src' => $image];
        })->merge($imgArray);
    }

}
