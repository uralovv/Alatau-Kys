<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
//        return parent::toArray($request);
        $product = Product::find($this->product_id);

        return [
            'ProductID' => $this->product_id,
            'Price' => $product->price,
            'Product Name' => $product->name,
            'Quantity' => $this->quantity
        ];
    }

}
