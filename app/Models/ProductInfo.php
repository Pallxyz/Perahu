<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductInfo extends Model
{
    protected $table = 'product_info'; // nama tabelmu 'product_info' tanpa s

    protected $fillable = ['category', 'title', 'content'];
}