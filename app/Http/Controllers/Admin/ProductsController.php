<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Utilities\ImageUploader;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Routing\Events\ResponsePrepared;
use App\Http\Requests\Admin\Products\StoreRequest;
use App\Http\Requests\Admin\Products\UpdateRequest;
use App\Utilities\FileRemover;

class ProductsController extends Controller
{

    public function all()
    {
        $products = Product::paginate(10);
        return view('admin.products.all', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.products.add', compact('categories'));
    }

    public function store(StoreRequest $request)
    {
        $validatedData = $request->validated();

        $admin = User::where('id', 4)->first();

        $createdProduct = Product::create([
            'title' => $validatedData['title'],
            'category_id' => $validatedData['category_id'],
            'description' => $validatedData['description'],
            'price' => $validatedData['price'],
            'owner_id' => $admin->id,
        ]);

        $result = $this->uploadImages($createdProduct,$validatedData);

        if($result)
            return back()->with('success', 'محصول اضافه شد');
        else
            return back()->with('failed', 'محصول اضافه نشد');
    }

    public function delete($product_id)
    {
        $product = Product::find($product_id)->delete();

        if ($product) {
            File::deleteDirectory(public_path('products/' . $product_id));
            File::deleteDirectory(storage_path('app/local_storage/products/' . $product_id));
        }

        return back()->with('success', 'محصول حذف شد');
    }

    public function edit($product_id)
    {
        $categories = Category::all();
        $product = Product::find($product_id);
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(UpdateRequest $request, $product_id)
    {
        $validatedData = $request->validated();

        $product = Product::find($product_id);

        $product->update([
            'title' => $validatedData['title'],
            'category_id' => $validatedData['category_id'],
            'price' => $validatedData['price'],
            'description' => $validatedData['description'],
        ]);

        $this->removeOldImages($product,$validatedData);
        $result = $this->uploadImages($product, $validatedData);

        if($result)
            return back()->with('success', 'محصول بروزرسانی شد');
        else
            return back()->with('failed', 'محصول بروزرسانی نشد');


    }

    # First way

    public function downloadDemo($product_id)
    {
        $product = Product::findOrFail($product_id);

        $demoPath = $product->demo_url;

        return Response::download($demoPath);
    }

    public function downloadsource($product_id)
    {
        $product = Product::findOrFail($product_id);

        $sourcePath = $product->source_url;

        return Response::download(storage_path('app/local_storage/') . $sourcePath);
    }

    # Second way

    // public function downloadDemo($product_id)
    // {
    //     $product = Product::findOrFail($product_id);

    //     return Response()->download(public_path($product->demo_url));
    // }

    // public function downloadsource($product_id)
    // {
    //     $product = Product::findOrFail($product_id);

    //     return response()->download(storage_path('app/local_storage/' . $product->source_url));
    // }

    private function uploadImages($createdProduct, $validatedData)
    {
        try {
            // DB::beginTransaction();

            $basePath = 'products/' . $createdProduct->id . '/';

            $sourceImageFullPath = null;

            $data = [];

            if (isset($validatedData['source_url'])) {

                $sourceImageFullPath = $basePath . 'source_url' . '_' . $validatedData['source_url']->getClientOriginalName();

                ImageUploader::upload($validatedData['source_url'], $sourceImageFullPath, 'local_storage');

                $data += ['source_url' => $sourceImageFullPath];
            }

            if (isset($validatedData['thumbnail_url'])) {

                $fullPath = $basePath . 'thumbnail_url' . '_' . $validatedData['thumbnail_url']->getClientOriginalName();

                ImageUploader::upload($validatedData['thumbnail_url'], $fullPath, 'public_storage');

                $data += ['thumbnail_url' => $fullPath];
            }

            if (isset($validatedData['demo_url'])) {

                $fullPath = $basePath . 'demo_url' . '_' . $validatedData['demo_url']->getClientOriginalName();

                ImageUploader::upload($validatedData['demo_url'], $fullPath, 'public_storage');

                $data += ['demo_url' => $fullPath];
            }

            $updatedProduct = $createdProduct->update($data);

            if (!$updatedProduct)
                throw new \Exception();

            if($updatedProduct)
                return true;
            // DB::commit();

        } catch (\Exception $e) {
            // return DB::rollBack();
            return false;
        }
    }

    private function removeOldImages($product,$validatedData)
    {
        if(isset($validatedData['source_url']))
        {
            $sourcePath = $product->source_url;
            FileRemover::remove($sourcePath,'local_storage');
        }

        if(isset($validatedData['thumbnail_url']))
        {
            $thumbnailPath = $product->thumbnail_url;
            FileRemover::remove($thumbnailPath,'public_storage');
        }

        if(isset($validatedData['demo_url']))
        {
            $demoPath = $product->demo_url;
            FileRemover::remove($demoPath,'public_storage');
        }
    }
}
