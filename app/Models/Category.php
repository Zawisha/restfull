<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $table = 'categories';
    protected $fillable = ['id','name'];

    public function products_table()
    {
        return $this->belongsToMany('App\Models\Product','categories_products');
    }
}
