<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $guarded=[];
    public $timestamps = false;
    public function variants()
    {
        return $this->hasMany(Variant::class);
    }
    public function carts()
    {
        return $this->hasMany(Cart::class,'product_id','id');
    }
}
