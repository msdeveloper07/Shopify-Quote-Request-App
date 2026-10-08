<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('product_image', 500)->nullable()->after('product_title');
            $table->string('product_url', 500)->nullable()->after('product_image');
            $table->enum('status', ['pending', 'in_progress', 'quotation_sent', 'completed', 'cancelled'])
                  ->default('pending')
                  ->change();
            $table->decimal('quoted_price', 10, 2)->nullable()->after('variant_price');
            $table->text('admin_notes')->nullable()->after('comments');
            $table->timestamp('quotation_sent_at')->nullable()->after('admin_notes');
            $table->string('quotation_sent_by')->nullable()->after('quotation_sent_at');
        });
    }

    public function down()
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn([
                'product_image',
                'product_url',
                'quoted_price',
                'admin_notes',
                'quotation_sent_at',
                'quotation_sent_by'
            ]);
            $table->string('status', 50)->default('pending')->change();
        });
    }
};