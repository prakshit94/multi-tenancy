<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | System Identifiers
            |--------------------------------------------------------------------------
            */

            $table->uuid('uuid')->unique();
            $table->string('customer_code')->unique(); // CUST-000001

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('display_name')->nullable();

            $table->string('mobile', 20)->unique();
            $table->string('email')->nullable();

            // Extra Phones
            $table->string('phone_number_2', 20)->nullable();
            $table->string('relative_phone', 20)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Source
            |--------------------------------------------------------------------------
            */

            $table->string('source', 50)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Classification
            |--------------------------------------------------------------------------
            */

            $table->enum('type', [
                'farmer',
                'buyer',
                'vendor',
                'dealer'
            ])->default('farmer');

            $table->enum('category', [
                'individual',
                'business'
            ])->default('individual');

            /*
            |--------------------------------------------------------------------------
            | Business Details
            |--------------------------------------------------------------------------
            */

            $table->string('company_name')->nullable();
            $table->string('gst_number')->nullable();
            $table->string('pan_number')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Agriculture Profile
            |--------------------------------------------------------------------------
            */

            $table->decimal('land_area', 10, 2)->nullable();
            $table->string('land_unit')->default('acre');

            // JSON Fields
            $table->json('crops')->nullable();
            $table->json('tags')->nullable();

            $table->string('irrigation_type')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Financial / Credit
            |--------------------------------------------------------------------------
            */

            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->decimal('outstanding_balance', 12, 2)->default(0);

            $table->date('credit_valid_till')->nullable();

            /*
            |--------------------------------------------------------------------------
            | KYC & Compliance
            |--------------------------------------------------------------------------
            */

            $table->string('aadhaar_last4')->nullable();

            $table->boolean('kyc_completed')->default(false);

            $table->timestamp('kyc_verified_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Engagement
            |--------------------------------------------------------------------------
            */

            $table->date('first_purchase_at')->nullable();
            $table->date('last_purchase_at')->nullable();

            $table->unsignedInteger('orders_count')->default(0);

            /*
            |--------------------------------------------------------------------------
            | Status & Control
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')->default(true);
            $table->boolean('is_blacklisted')->default(false);

            $table->text('internal_notes')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            $table->timestamps();
            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | INDEXES (Performance Optimized)
            |--------------------------------------------------------------------------
            */

            // Basic Search / Lookup
            $table->index('email');
            $table->index('mobile');
            $table->index('source');

            // Business Details
            $table->index('gst_number');
            $table->index('pan_number');
            $table->index('company_name');

            // Purchase Tracking
            $table->index('first_purchase_at');
            $table->index('last_purchase_at');

            // Financial Queries
            $table->index('credit_valid_till');
            $table->index('outstanding_balance');

            // Status Queries
            $table->index('is_active');
            $table->index('is_blacklisted');

            // Audit
            $table->index('created_by');
            $table->index('updated_by');

            // Soft Deletes
            $table->index('deleted_at');

            /*
            |--------------------------------------------------------------------------
            | Composite Indexes
            |--------------------------------------------------------------------------
            */

            // Common filtering
            $table->index(['type', 'is_active']);

            $table->index([
                'category',
                'is_active'
            ]);

            $table->index([
                'source',
                'is_active'
            ]);

            // Financial Dashboard
            $table->index([
                'outstanding_balance',
                'is_active'
            ]);

            // Customer Activity
            $table->index([
                'last_purchase_at',
                'is_active'
            ]);

            $table->index([
                'orders_count',
                'is_active'
            ]);

            // Blacklist
            $table->index([
                'is_blacklisted',
                'is_active'
            ]);

            /*
            |--------------------------------------------------------------------------
            | Fulltext Search
            |--------------------------------------------------------------------------
            |
            | Requires MySQL 5.7+ / MySQL 8+
            |
            */

            $table->fullText(
    ['first_name', 'last_name', 'display_name', 'company_name'],
    'customers_search_ft'
);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};