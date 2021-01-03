<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sales_info extends Model
{
    use HasFactory;

    protected $table = 'sales_info';
    protected $primaryKey = 'product_code';
    protected $fillable = ['product_code','eng_name','initial_stock','stock','isnew'];
    public function product_info()
    {
        return $this->belongsTo('App\Models\Product_info');
    }
}
