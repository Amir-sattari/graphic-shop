<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        try {
            $products = null;

            if (isset($request->filter, $request->action, $request->value)) {
                $products = $this->findFilters($request?->filter, $request?->action, $request?->value) ?? Product::all();

            } else if (isset($request->filter, $request->action)) {
                $products = $this->findFilters($request?->filter, $request?->action) ?? Product::all();

            } else
                $products = Product::all();


            if ($request->has('search'))
                $products = Product::where('title', 'LIKE', '%' . $request->input('search') . '%')->get();

            if ($request->has('search-product'))
                $products = Product::where('title', 'LIKE', '%' . $request->input('search-product') . '%')->get();


            $categories = Category::all();
            return view('frontend.products.all', compact('products', 'categories'));
        } catch (\Exception $e) {

            $products = Product::all();
            $categories = Category::all();

            return view('frontend.products.all', compact('products', 'categories'));
        }
    }

    public function show($product_id)
    {
        $product = Product::find($product_id);
        $relatedProducts = Product::where('category_id', $product->category_id)->where('id', '<>', $product->id)->take(4)->get();
        return view('frontend.products.show', compact('product', 'relatedProducts'));
    }

    private function findFilters(string $className, string $methodName, string $value = null)
    {
        $baseNameSpace = "App\Http\Controllers\Filters\\";

        $className = $baseNameSpace . (ucfirst($className) . 'Filter');

        if (!class_exists($className))
            return null;

        $object = new $className;

        if (!method_exists($object, $methodName))
            return null;

        return $object->{$methodName}($value);
    }
}
