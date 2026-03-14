<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('allow_oversell')->default(false)->after('manage_stock');
            $table->integer('oversell_limit')->nullable()->default(0)->after('allow_oversell');
            $table->boolean('is_sku_enabled')->default(true)->after('sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['allow_oversell', 'oversell_limit', 'is_sku_enabled']);
        });
    }
};
