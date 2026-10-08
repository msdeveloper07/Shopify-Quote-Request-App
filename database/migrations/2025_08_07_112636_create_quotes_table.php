<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('shop_domain');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone', 32);
            $table->string('product_title', 255);
            $table->string('product_id', 100)->nullable();
            $table->string('variant_id', 100)->nullable();
            $table->text('comments')->nullable();
            $table->string('selected_variant', 255)->nullable();
            $table->string('variant_name', 255)->nullable();
            $table->decimal('variant_price', 10, 2)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('status', 50)->default('pending');
            $table->integer('quantity')->default(1);
            $table->string('shopify_customer_id')->nullable();
            $table->timestamps();
            
            $table->index('shop_domain');
            $table->index('email');
            $table->index('created_at');
            $table->index(['shop_domain', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('quotes');
    }
};