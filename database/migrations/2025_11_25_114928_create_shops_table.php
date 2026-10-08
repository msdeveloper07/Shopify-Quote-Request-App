<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('shopify_domain')->unique();
            $table->text('access_token');
            $table->string('scopes')->nullable();
            $table->text('associated_user')->nullable();
            $table->timestamp('installed_at')->nullable();
            $table->timestamps();
            
            $table->index('shopify_domain');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shops');
    }
};