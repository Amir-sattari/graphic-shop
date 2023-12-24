<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Categories\StoreRequest;
use App\Http\Requests\Admin\Categories\UpdateRequest;

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

    public function delete($category_id)
    {
        $category = Category::find($category_id)->delete();
        return back()->with('success','Category deleted');
    }

    public function edit($category_id)
    {
        $category = Category::find($category_id);
        return view('admin.categories.edit',compact('category'));
    }

    public function update(UpdateRequest $request,$category_id)
    {
        $validatedData = $request->validated();
        $category = Category::find($category_id);

        $updatedCategory = $category->update([
            'title' => $validatedData['title'],
            'slug' => $validatedData['slug'],
        ]);

        if(!$updatedCategory)
            return back()->with('failed','Category does not updated');

        return back()->with('success','Category updated');
    }
}
