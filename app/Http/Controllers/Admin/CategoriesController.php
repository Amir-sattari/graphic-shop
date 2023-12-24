<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Categories\StoreRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(StoreRequest $request)
    {
        $validatedData = $request->validated();

        $createdCategory = Category::create([
            'title' => $validatedData['title'],
            'slug' => $validatedData['slug'],
        ]);

        if(!$createdCategory)
            return back()->with('failed','Category does not created');

        return back()->with('success','Category created');
    }

    public function all()
    {
        $categories = Category::paginate(10);
        return view('admin.categories.all',compact('categories'));
    }
}
