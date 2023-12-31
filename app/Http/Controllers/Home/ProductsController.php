<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function index()
    {
        $products = Product::all();
        $categories = Category::all();
        return view('frontend.products.all',compact('products','categories'));
    }

    public function show($product_id)
    {
        $product = Product::find($product_id);
        $relatedProducts = Product::where('category_id',$product->category_id)->where('id', '<>',$product->id)->take(4)->get();
        return view('frontend.products.show',compact('product','relatedProducts'));
    }
}
