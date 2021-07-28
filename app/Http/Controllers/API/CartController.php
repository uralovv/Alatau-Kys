<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartItem;
use Illuminate\Http\Request;
use App\Models\Cart;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $cart = Cart::create([
            'key' => md5(uniqid(rand(), true)),


        ]);
        return response()->json([
            'Message' => 'A new cart have been created for you!',
            'key' => $cart->key,
        ], 201);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Cart $cart, Request $request)
    {
        //
        $validator = Validator::make(
            $request->all(),[
              'cartKey' => 'bail|required'
            ]
        );
        if ($validator->fails()){
            throw new \Exception($validator->errors()->first(),400);
        }
        $cartKey = $request->input('cartKey');
        if ($cart->key == $cartKey){
            return response([
                'Items in Cart' => new CartItem($cart->items),
            ],200);
        }
        else{
            throw new \Exception('Неверный uuid корзины !');
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}


//namespace App\Http\Controllers\API;
//
//use App\Http\Controllers\Controller;
//use App\Http\Resources\CartItem;
//use App\Models\Cart;
//use App\Models\Product;
//use Illuminate\Database\Eloquent\ModelNotFoundException;
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Validator;
//
//class CartController extends Controller
//{
//    //
//    public function store(Request $request)
//    {
//        $cart = Cart::create([
//            'id' => md5(uniqid(rand(), true)),
//            'key' => md5(uniqid(rand(), true))
//        ]);
//
//        return response()->json([
//            'message' => 'Корзина создана !',
//            'cartToken' => $cart->id,
//            'cartKey' => $cart->key,
//        ], 201);
//    }
//
//    public function show(Cart $cart, Request $request)
//    {
//        $validator = Validator::make($request->all(), [
//            'cartKey' => 'bail|required',
//        ]);
//
//        if ($validator->fails()) {
//            return response()->json([
//                'errors' => $validator->errors(),
//            ], 400);
//        }
//
//        $cartKey = $request->input('cartKey');
//        if ($cart->key == $cartKey) {
//            return response()->json([
//                'cart' => $cart->id,
//                'Items in Cart' => new CartItem($cart->items),
//            ], 200);
//
//        } else {
//
//            return response()->json([
//                'message' => 'The CartKey you provided does not match the Cart Key for this Cart.',
//            ], 400);
//        }
//
//    }
//
//    public function addProducts(Cart $cart, Request $request)
//    {
//        $validator = Validator::make(
//            $request->all(), [
//            'cartKey' => 'bail|required',
//            'productId' => 'bail|required',
//            'quantity' => 'bail|required|numeric|min:1|max:10',
//        ],
//            [
//                'cartKey.required' => 'Введите uuid корзины !',
//                'productId.required' => 'Введите id продукта !',
//                'productId' => 'Введите число для id продукта !',
//                'quantity.required' => 'Введите количество продукта !',
//                'quantity.min' => 'Минимальное количество для заказа - 1',
//                'quantity.max' => 'Максимальное количество для заказа - 10',
//            ]
//        );
//
//        if ($validator->fails()) {
//            throw new \Exception($validator->errors()->first());
//        }
//
//        $cartKey = $request->input('cartKey');
//        $productId = $request->input('productId');
//        $quantity = $request->input('quantity');
//
//        if ($cart->key == $cartKey) {
//            //Check if the proudct exist or return 404 not found.
//            try {
//                $Product = Product::findOrFail($productId);
//            } catch (ModelNotFoundException $e) {
//                return response()->json([
//                    'message' => 'The Product you\'re trying to add does not exist.',
//                ], 404);
//            }
//
//            //check if the the same product is already in the Cart, if true update the quantity, if not create a new one.
//            $cartItem = \App\Models\CartItem::where(['cart_id' => $cart->getKey(), 'product_id' => $productId])->first();
//            if ($cartItem) {
//                $cartItem->quantity = $quantity;
//                \App\Models\CartItem::where(['cart_id' => $cart->getKey(), 'product_id' => $productId])->update(['quantity' => $quantity]);
//            } else {
//                \App\Models\CartItem::create(['cart_id' => $cart->getKey(), 'product_id' => $productId, 'quantity' => $quantity]);
//            }
//
//            return response()->json(['message' => 'The Cart was updated with the given product information successfully'], 200);
//
//        } else {
//
//            return response()->json([
//                'message' => 'The CarKey you provided does not match the Cart Key for this Cart.',
//            ], 400);
//        }
//    }
//}
