<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\WishlistCollection;
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
            try{
                $product = Product::findOrFail($productId);
            }catch (ModelNotFoundException $e){
                throw new \Exception('Товар не найден !');
            }
            Wishlist::create([
                'user_id' => $auth,
                'product_id' => $productId
            ]);
            return response([
               'message' => 'Товар добавлен в избранные !'
            ]);
        }else{
            throw new \Exception('User not found !');
        }
    }

    public function delete(Request $request){
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
            $remove_product = Wishlist::where('user_id',$auth)->where('product_id',$productId)->first();
            if ($remove_product){
                $remove_product->delete();
                return response([
                    'message' => 'Товар был удален из избранных !'
                ]);
            }
            else{
                throw new \Exception('Товар не найден !');
            }
        }
        else{
            throw new \Exception('Ошибка !');

        }
    }
    public function view(){
        $auth = Wishlist::select('user_id')->where('user_id',auth('api')->user()->id)->get();

        foreach ($auth as $item){
//            $product_name = $item->products()->name;
        }
        return response([
            'message' => $auth,
//            'products' => $product_name
        ]);
    }
}
