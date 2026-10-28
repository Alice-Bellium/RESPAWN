<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();

        return CategoryResource::collection($categories);
    }

    public function show(Category $category)
    {
        $products = $category->products;
    }

    public function create()
    {
        $categories = Category::all();
    }

    public function store()
    {
        $categories = Category::all();
    }

    public function edit(Category $category)
    {
        $categories = Category::all();
    }

    public function update(Request $request, Category $category)
    {
        $categories = Category::all();
    }

    public function destroy(Request $request, Category $category)
    {
        $categories = Category::all();
    }
}
