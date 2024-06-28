<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DefaultCategory;
use App\Http\Requests\UpdateStoreDefaultCategoryRequest;
use App\Http\Resources\DefaultCategoryResource;

class DefaultCategoryController extends Controller
{
    public function index(){
        return DefaultCategoryResource::collection(DefaultCategory::orderBy('name','asc')->paginate(10));
    }

    public function show(DefaultCategory $defaultCategory){
        return new DefaultCategoryResource($defaultCategory);
    }

    public function store(UpdateStoreDefaultCategoryRequest $request){
        $newDefaultCategory = DefaultCategory::create($request->validated()); 
        return new DefaultCategoryResource($newDefaultCategory)  ;
    }

    public function update(UpdateStoreDefaultCategoryRequest $request, DefaultCategory $defaultCategory){
        $defaultCategory->update($request->validated());
        return new DefaultCategoryResource($defaultCategory);
    }

    public function delete(DefaultCategory $defaultCategory){
        // soft delete se tiver a ser usada por uma categoria, senao full delete da db
        $defaultCategory->delete();
        return new DefaultCategoryResource($defaultCategory);
    }
}
