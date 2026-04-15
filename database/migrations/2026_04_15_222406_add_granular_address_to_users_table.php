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
        Schema::table('users', function (Blueprint $table) {
            $table->string('village')->nullable()->after('address');
            $table->string('pincode')->nullable()->after('village');
            $table->string('post_office')->nullable()->after('pincode');
            $table->string('taluka')->nullable()->after('post_office');
            $table->string('district')->nullable()->after('taluka');
            $table->string('state')->nullable()->after('district');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'village',
                'pincode',
                'post_office',
                'taluka',
                'district',
                'state'
            ]);
        });
    }
};
