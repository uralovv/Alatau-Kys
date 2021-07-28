<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_items', function (Blueprint $table) {
//            $table->string('cart_id');
//            $table->unsignedInteger('product_id');
//            $table->unsignedInteger('quantity');
//            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
//            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
//            $table->primary(array('cart_id', 'product_id'));
            $table->id();
            $table->bigInteger('product_id')->unsigned();
            $table->string('cart_key');
            $table->integer('quantity')->unsigned();

            $table->foreign('cart_key')
                ->references('key')
                ->on('carts');

            $table->foreign('product_id')
                ->references('id')
                ->on('products');

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
        Schema::dropIfExists('cart_items');
    }
}
