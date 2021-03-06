<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;
    protected $fillable = ['product_id', 'cart_key', 'quantity'];
    protected $table = 'cart_items';

    public function cart(){
        return $this->belongsTo(Cart::class,'cart_key');
    }
    public function product(){
        return $this->hasOne(Product::class);
    }
}
