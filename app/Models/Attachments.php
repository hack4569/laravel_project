<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachments extends Model
{
    use HasFactory;
    protected $table = 'attachments';
    protected $primaryKey = 'id';
    protected $fillable = ['filename','byte','mime','product_code','originfilename'];
    protected  $foreignKey = 'product_code';
    public function product_info(){
        return $this->belongsTo('App\Models\Product_info');
    }
}
