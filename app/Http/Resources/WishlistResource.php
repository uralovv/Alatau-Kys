<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;

class WishlistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $product = Product::findOrFail($this->product_id);
//        return parent::toArray($request);
        return [
//          'Product ID' => $this->product_id,
          'Product Name' => $product->name,
          'Product Price' => $product->price
        ];
    }
}
