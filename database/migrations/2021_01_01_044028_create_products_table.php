<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('product_code');
            $table->string('eng_name',50);
            $table->string('kor_name',50);
            $table->string('fst_cate',10);
            $table->string('snd_cate',10);
            $table->string('origin',50);
            $table->string('type',50);
            $table->string('personality',255);
            $table->integer('in_price');
            $table->integer('out_price');
            $table->string('descr',255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
