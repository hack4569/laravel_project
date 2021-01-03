<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_info', function (Blueprint $table) {
            $table->increments('key_id');
            $table->integer('product_code');
            $table->string('eng_name',50);
            $table->integer('initial_stock');
            $table->integer('stock');
            $table->integer('quantity');
            $table->integer('count');

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
        Schema::dropIfExists('sales_info');
    }
}
