<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartItemCollection;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Models\Cart;
use Illuminate\Support\Facades\Validator;
use App\Models\CartItem;

class CartController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
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
    public function addProducts($key, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'productId' => 'bail|required|numeric',
            'quantity' => 'bail|required|numeric|min:1|max:10'
        ],
            [
                'productId.required' => 'Введите идентификатор продукта !',
                'productId.numeric' => 'Неверный формат идентификатора продукта !',
                'quantity.required' => 'Введите количество продукта !',
                'quantity.numeric' => 'Неверный формат количества продукта !',
                'quantity.min' => 'Минимальное колиество - 1 !',
                'quantity.max' => 'Максимальное колиество - 10 !',
            ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        $productId = $request->input('productId');
        $quantity = $request->input('quantity');

        $cart = Cart::select(['id', 'key'])->find($key);

        if ($cart) {
            try {
                $product = Product::findOrFail($productId);
            } catch (ModelNotFoundException $e) {
                throw new \Exception('Товар не найден !');
            }
            $cartItem = CartItem::where(['cart_key' => $cart->key, 'product_id' => $productId])->first();
            if ($cartItem) {
                $cartItem->quantity = $quantity;
                CartItem::where(['cart_key' => $cart->key, 'product_id' => $productId])->update(['quantity' => $quantity]);
            } else {
                CartItem::create(['cart_key' => $cart->key, 'product_id' => $productId, 'quantity' => $quantity]);
            }
            return response([
                'message' => 'Product added to your cart !'
            ]);
        } else {
            throw new \Exception('Wrong cart key', 400);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($key)
    {
        $cart = Cart::select(['id', 'key'])->find($key);

        if (!$cart) {
            throw new \Exception('Неверный uuid корзины !');
        }
        $total = (float) 0.0;
        $items = $cart->items;

        foreach ($items as $item) {
            $product =Product::find($item->product_id);
            $price = $product->price;
//            $total = $total + ($price * $item->quantity) . ' ' . 'Тенге';
            $total = $total + ($price * $item->quantity);
//            $total.= ' Тенге ';
        }

        return response([
            'cart' => $cart->key,
            'Items in cart' => new CartItemCollection($cart->items),
            'Total' => $total
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $key
     * @return \Illuminate\Http\Response
     */
    public function removeProduct($key, Request $request)
    {
        $validator = Validator::make(
            $request->all(), [
            'productId' => 'bail|required|numeric'
        ],
            [
                'productId.required' => 'Введите идентификатор продукта !',
                'productId.numeric' => 'Неверный формат идентификатора продукта !',
            ]
        );
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        $productId = $request->input('productId');
        $cart = Cart::select(['id', 'key'])->find($key);
        if ($cart) {
            try {
                $product = Product::findOrFail($productId);
            } catch (ModelNotFoundException $e) {
                throw new \Exception('Товар не найден !');
            }
            $cartItem = CartItem::where(['cart_key' => $cart->key, 'product_id' => $productId])->first();

            if ($cartItem){
                $cartItem->delete();
                return response([
                   'message' => 'Товар удален из корзины !'
                ]);
            }else{
                throw new \Exception('Товар не найден в корзине  !');
            }
        }
        else{
            throw new \Exception('Неверный uuid корзины !');
        }
    }
}
