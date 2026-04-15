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
            $table->date('date_of_birth')->nullable()->after('bio');
            $table->date('joining_date')->nullable()->after('date_of_birth');
            $table->string('employee_id')->nullable()->after('joining_date');
            $table->string('department')->nullable()->after('employee_id');
            $table->string('gender')->nullable()->after('department');
            $table->text('address')->nullable()->after('gender');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'date_of_birth',
                'joining_date',
                'employee_id',
                'department',
                'gender',
                'address'
            ]);
        });
    }
};
