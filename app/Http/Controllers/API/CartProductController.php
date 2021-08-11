<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CartProduct;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\CartProductCollection;

class CartProductController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function view(){
//        $products = CartProduct::where('user_id',auth('api')->user()->id)
//            ->with(array('products'=> function($query){
//                $query->select('id','name','price','image');
//            }))->get();
//
//        $data = array();
//        foreach ($products as $product) {
//            $data[] = $product->products;
//
//        }
//        return response([
//           'data' => $data
//        ]);
        $items = CartProduct::where('user_id',auth('api')->user()->id)->get();
        $total = (float) 0.0;

        foreach ($items as $item){
            $product = Product::findOrFail($item->product_id);
            $price =  $product->price;
            $total = $total + ($price * $item->quantity);
        }

        return response([
            'data' => new CartProductCollection($items),
            'total' => $total
        ]);
    }

    public function store(Request $request)
    {
        $productId = $request->input('productId');
        $user = auth()->user()->id;
        $quantity = $request->input('quantity');

        $validator = Validator::make(
            $request->all(),
            [
                'productId' => 'bail|required|numeric',
                'quantity' => 'bail|required|numeric|min:1|max:10'
            ],
            [
                'productId.required' => 'Укажите ID продукта !',
                'productId.numeric' => 'Неверный формат продукта !',
                'quantity.required' => 'Укажите количество продукта !',
                'quantity.min' => 'Минимальное значение для количества продукта - 1',
                'quantity.max' => 'Максимальное значение для количества продукта - 10'
            ]
        );
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }
        if ($user) {
            try {
                Product::findOrFail($productId);
            } catch (ModelNotFoundException $e) {
                throw new \Exception('Товар не найден !');
            }
            $duplicate = CartProduct::where('user_id', $user)->where('product_id', $productId)->first();
            if ($duplicate){
                $duplicate->quantity = $quantity;
                CartProduct::where(['user_id' => $user, 'product_id' => $productId])->update(['quantity' => $quantity]);
                return response(['data' => 'Количество товара было обновлено !']);
            }
            else {
                CartProduct::create([
                    'user_id' => $user,
                    'product_id' => $productId,
                    'quantity' => $quantity
                ]);
                return response([
                    'data' => 'Товар доваблен в корзину !'
                ]);
            }
        } else {
            throw new \Exception('Пользователь не найден !');
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
            $delete_product = CartProduct::where('user_id', $auth)->where('product_id', $productId)->first();

            if ($delete_product) {
                $delete_product->delete();
                return response([
                    'data' => 'Товар был удален из корзины !'
                ]);
            } else {
                throw new \Exception('Товар не найден !');
            }
        }else{
            throw new \Exception('Произошла ошибка, попробуйте позже !');
        }
    }
}
