<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\WishlistCollection;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WishlistController extends Controller
{
    //
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(), [
            'productId' => 'bail|required|numeric'
        ],
            [
                'productId.required' => 'Введите Ид продукта !',
                'productId.numeric' => 'Неверный формат !'
            ]
        );
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }
        $productId = $request->input('productId');
        $auth = auth('api')->user()->id;

        if ($auth) {
            try {
                $product = Product::findOrFail($productId);
            } catch (ModelNotFoundException $e) {
                throw new \Exception('Товар не найден !');
            }
            $duplicate = Wishlist::where('user_id', $auth)->where('product_id', $productId)->first();
            if (!$duplicate) {
                Wishlist::create([
                    'user_id' => $auth,
                    'product_id' => $productId
                ]);
                return response([
                    'message' => 'Товар добавлен в избранные !'
                ]);
            } else {
                throw new \Exception('Товар уже был добавлен в избранные !');
            }
        } else {
            throw new \Exception('User not found !');
        }
    }

    public function delete(Request $request)
    {
        $validator = Validator::make(
            $request->all(), [
            'productId' => 'bail|required|numeric'
        ],
            [
                'productId.required' => 'Введите Ид продукта !',
                'productId.numeric' => 'Неверный формат !'
            ]
        );
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }
        $productId = $request->input('productId');
        $auth = auth('api')->user()->id;

        if ($auth) {
            try {
                $product = Product::findOrFail($productId);
            } catch (ModelNotFoundException $e) {
                throw new \Exception('Товар не найден !');
            }
            $remove_product = Wishlist::where('user_id', $auth)->where('product_id', $productId)->first();
            if ($remove_product) {
                $remove_product->delete();
                return response([
                    'message' => 'Товар был удален из избранных !'
                ]);
            } else {
                throw new \Exception('Товар не найден !');
            }
        } else {
            throw new \Exception('Ошибка !');

        }
    }

    public function view()
    {
        $products = Wishlist::where('user_id',auth('api')->user()->id)
            ->select('product_id')->with(array('products' => function($query) {
                $query->select('id','name','price','image');
            }))->get();

//        $products = Wishlist::where('user_id',auth('api')->user()->id)
//            ->select('product_id')->with('products')->get();

        $data = array();

        foreach ($products as $product) {
            $data [] = $product->products;
        }
//            foreach ($product->products as $item)
        return response([
            'Products' => $data
        ]);

    }
}
