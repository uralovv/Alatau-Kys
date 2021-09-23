<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function view($id)
    {
        /** @var Product|null $product */
        $product = Product::select(['id', 'name', 'additional_images', 'description', 'price'])->find($id);
        $images = Product::select(['additional_images'])->where($id)->get();

        if (!$product) {
            throw new \Exception('Товар не найден !');
        }
        return response()->json(['Data' => $product,'$images'], 200);
    }


    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        if (!$query = $request->input('query')) {
            return response()->json([
                'result' => 'Товар не найден !'
            ]);
        }

        $productIds = Product::selectRaw(
            'products.id, CHAR_LENGTH(REGEXP_REPLACE(REGEXP_REPLACE(LOWER(REPLACE(CONCAT(products.name, ' .
            'products.description, categories.name), \' \', \'\')), ?, \'~\'), \'[^~]\', \'\')) as frequency',
            [str_replace(' ', '|', $query)]
        )
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->having('frequency', '>', 0)
            ->orderByDesc('frequency')->get()->modelKeys();

        $products = Product::select(['id', 'name', 'description', 'price', 'image'])->find($productIds);

            return response()->json([
                'Результаты поиска: ' => $products
            ]);
        }

}
