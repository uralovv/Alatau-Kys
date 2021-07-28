<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    protected $fillable = ['key','id'];
    protected $primaryKey = 'key';
    public $incrementing = false;


    public function items(){
        return $this->hasMany(CartItem::class);
    }
}
