<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoriesController extends Controller
{
    //
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {

        $totalCount = Category::count();
        $categories = Category::select(['id','name'])->limit(10)->get();

        return response()->json([
           'categories' => $categories,
           'count' => $totalCount
        ]);
    }

    public function view(Request $request, $categoryId)  {

        $category = Category::findOrFail($categoryId);
        $totalCount = Product::where('category_id',$categoryId)->count();
        $products = $category->products()
            ->select(['id','name','description','price','image','additional_images'])->limit(10)->get();

        if (!$category){
            throw new \Exception('Категория не найдена !');
        }

        return response()->json([
            'items'=>$products,
            'total_count' => $totalCount
        ]);

    }
}
