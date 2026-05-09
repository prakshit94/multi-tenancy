<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {

        /*
        |--------------------------------------------------------------------------
        | ORDER VERIFICATIONS
        |--------------------------------------------------------------------------
        */

        Schema::create('order_verifications', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Verification Details
            |--------------------------------------------------------------------------
            */

            $table->string('status');
            // unverified, pending_followup, verified, rejected

            $table->text('remarks')->nullable();

            $table->dateTime('next_followup_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEXES (Performance Optimized)
            |--------------------------------------------------------------------------
            */

            // Relationships
            $table->index('order_id');

            $table->index('user_id');

            // Status & Followups
            $table->index('status');

            $table->index('next_followup_at');

            // Audit
            $table->index('created_at');

            /*
            |--------------------------------------------------------------------------
            | Composite Indexes
            |--------------------------------------------------------------------------
            */

            // Order verification history
            $table->index([
                'order_id',
                'status'
            ]);

            // User verification tracking
            $table->index([
                'user_id',
                'status'
            ]);

            // Pending followups dashboard
            $table->index([
                'status',
                'next_followup_at'
            ]);

            // Verification activity reports
            $table->index([
                'created_at',
                'status'
            ]);

            /*
            |--------------------------------------------------------------------------
            | FullText Search
            |--------------------------------------------------------------------------
            */

            $table->fullText([
                'remarks'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_verifications');
    }
};