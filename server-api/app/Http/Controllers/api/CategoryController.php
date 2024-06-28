<?php

namespace App\Http\Controllers\api;

use App\Models\Vcard;
use App\Models\Category;
use App\Models\Transaction;
use App\Http\Resources\CategoryResource;
use App\Http\Requests\StoreUpdateCategoryRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{

    public function index(){
        return CategoryResource::collection(Category::orderBy('name','asc')->paginate(10));
    }

    public function show(Category $category){
        return new CategoryResource($category);
    }

    public function store(StoreUpdateCategoryRequest $request){
        $validatedRequest = $request->validated();

        $existingCategory = Category::where('vcard', $validatedRequest['vcard'])
        ->where('type', $validatedRequest['type'])->where('name', $validatedRequest['name'])->first();
        
        if ($existingCategory) {
            return response()->json(['error' => 'Category already exists'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $newCategory = Category::create($validatedRequest); 

        return new CategoryResource($newCategory);
    }

    public function update(StoreUpdateCategoryRequest $request, Category $category){

        $validatedRequest = $request->validated();

        $existingCategory = Category::where('vcard', $validatedRequest['vcard'])
        ->where('type', $validatedRequest['type'])->where('name', $validatedRequest['name'])->first();
        
        if ($existingCategory) {
            return response()->json(['error' => 'Category already exists'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $category->update($validatedRequest);
        return new CategoryResource($category);
    }

    public function delete(Category $category){
        // soft delete se tiver a ser usada por uma categoria, senao full delete da db
        count($category->transactions) != 0 ? $category->delete() : $category->forceDelete();

        return new CategoryResource($category);
    }

    // public function getCategoryOfTransaction(Transaction $transaction){
    //     return $transaction->categories;
    // }
}