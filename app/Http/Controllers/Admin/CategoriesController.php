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
            return back()->with('failed','دسته بندی اضافه نشد');

        return back()->with('success','دسته بندی اضافه شد');
    }

    public function all()
    {
        $categories = Category::paginate(10);
        return view('admin.categories.all',compact('categories'));
    }

    public function delete($category_id)
    {
        Category::find($category_id)->delete();
        return back()->with('success','دسته بندی حذف شد');
    }

    public function edit($category_id)
    {
        $category = Category::find($category_id);
        return view('admin.categories.edit',compact('category'));
    }

    public function update(UpdateRequest $request,$category_id)
    {
        $validatedData = $request->validated();

        $category = Category::find($category_id)->update([
                'title' => $validatedData['title'],
                'slug' => $validatedData['slug'],
            ]);

        if(!$category)
            return back()->with('failed','دسته بندی بروزرسانی نشد');

        return back()->with('success','دسته بندی بروزرسانی شد');
    }
}
