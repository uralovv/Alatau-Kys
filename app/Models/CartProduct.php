<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartProduct extends Model
{
    use HasFactory;

    protected $fillable = ['product_id','user_id','quantity'];

    public function products(){
        return $this->belongsTo(Product::class,'product_id');
    }
    public function items(){
        return $this->hasMany(Product::class);
    }
}
