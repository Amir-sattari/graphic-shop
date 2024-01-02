<?php

namespace App\Http\Controllers\Filters;

use App\Models\Product;

class PriceFilter
{
    public function filterProductByPrice($value)
    {
        $value = explode('to', $value);
        return Product::whereBetween('price', [$value[0], $value[1]])->get() ?? Product::all();
    }
}
