<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $fillable = ['id','name', 'price', 'published', 'deleted'];

    public function categories_table()
    {
        return $this->belongsToMany('App\Models\Category','categories_products');
    }

}

